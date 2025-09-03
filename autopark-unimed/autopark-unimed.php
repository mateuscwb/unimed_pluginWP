<?php

/**
 * @link              www.abrigosoftware.com.br
 * @since             1.0.0
 * @package           Abgsoft_Autopark_Unimed
 *
 * @wordpress-plugin
 * Plugin Name:       Unimed Promoção
 * Description:       Plugin auxiliar para a Wordpress desenvolvido por Abrigo Software.
 * Version:           1.0.0
 * Author:            Abrigo Software
 * Author URI:        www.abrigosoftware.com.br
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       abgsoft-autopark-unimed
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
		die;
}

define( 'PLUGIN_VERSION', '1.0.0' );
define( 'UPLOAD_PATH', wp_get_upload_dir()['basedir'] .'/abgsoft-uploads/' );
//define( 'UPLOAD_PATH', plugin_dir_url( __FILE__ ) . 'tmp-qrcodes/' );

add_action( 'wpcf7_before_send_mail', 'tratar_solicitacao', 1, 3 );
add_action( 'wp_enqueue_scripts', 'scripts' );

// Admin settings page
add_action( 'admin_menu', 'abgsoft_autopark_add_admin_menu' );
add_action( 'admin_init', 'abgsoft_autopark_settings_init' );

add_filter('wpcf7_ajax_json_echo', 'tratar_resposta', 1, 1);
add_action('wp_footer', 'add_modal_qrcode');

function add_modal_qrcode() {
?>
<style>
#qrcodeModal {
	margin-top: 160px;
}
</style>
<div id="qrcodeModal" class="modal" style="text-align: center;">
	<h3>Código promocional gerado com sucesso!</h3>
	<p><strong>Este voucher é valido para um único uso e expira em 30 dias.</strong></p>
	<p>Apresente o QRCode no CAIXA para efetuar o pagamento do estacionamento.</p>
	<p>Ele também foi enviado para o e-mail cadastrado, podendo ser exibido em seu dispositivo móvel.</p>
	<img id="qrcodeImg" src="" />
	<p>Para fazer o download agora, 
	<a href="#" download="qrcode-autopark.png" id="qrcodeDownload">clique aqui</a></p>
</div>
<?php
};


function scripts() {
	wp_enqueue_style( 'jquerymodal', plugin_dir_url( __FILE__ ) . 'js/jquery.modal.min.css');
	wp_enqueue_script( 'jquerymodal', plugin_dir_url( __FILE__ ) . 'js/jquery.modal.min.js', array( 'jquery' ), null, true );
	wp_enqueue_script( 'abgsoft-autopark-unimed', plugin_dir_url( __FILE__ ) . 'js/scripts.js', array( 'jquery' ), PLUGIN_VERSION, true );

	// Pass settings to JS (CF7 form ID)
	$cf7_form_id = get_option( 'abgsoft_autopark_cf7_form_id', '1703' );
	wp_localize_script( 'abgsoft-autopark-unimed', 'ABGSOFT_AUTOPARK_SETTINGS', array(
		'cf7_form_id' => (string) $cf7_form_id,
	) );
}

function logFile($msg){
	$logDir = plugin_dir_path( __FILE__ ) . '/log/';
	if ( ! file_exists( $logDir ) ) {
		// Tenta criar o diretório de logs
		if ( function_exists('wp_mkdir_p') ) { wp_mkdir_p( $logDir ); }
		else { @mkdir( $logDir, 0755, true ); }
	}
	$filename = $logDir . date("Ymd",time()).'.log';
	$fd = @fopen($filename,"a");
	$str = "[".date("d/m/Y h:i:s",time())."] ".$msg;
	if ($fd) {
		fwrite($fd, $str."\n");
		fclose($fd);
	}
}

function tratar_resposta($items){
	if ( isset($_POST['qrcode']) ) {
		$items['qrcode'] = $_POST['qrcode'];	
	}
	return $items;
}

function tratar_solicitacao( $contact_form, $cancelEmail, $submission ){
		logFile('Verificando a solicitação...');
		$data = $submission->get_posted_data();
		if(isset($data['nome']) && isset($data['cpf']) && isset($data['email']) && isset($data['placa']) && isset($data['unidade'])){
			ob_start();
			var_dump($data);
			logFile(basename(__FILE__) . "\n" . ob_get_clean());
			$promotion_id = (int) get_option( 'abgsoft_autopark_promotion_id', 1 );
			$params = array(
				'promotion_id' => $promotion_id,
				'name' => $data['nome'],
				'cpf' => $data['cpf'],
				'email' => $data['email'],
				'license_plate' => $data['placa'],
				'branch_id' => is_array($data['unidade']) ? $data['unidade'][0] : $data['unidade']
			);
			$response = consume_api($params);
			if( is_array($response) && !empty($response['qrcode']) ){
				//logFile('QRCode library load..');
				require_once( dirname( __FILE__ ) . '/libs/phpqrcode.inc' );
				// Garante diretório de upload existente
				if ( ! file_exists( UPLOAD_PATH ) ) {
					if ( function_exists('wp_mkdir_p') ) { wp_mkdir_p( UPLOAD_PATH ); }
					else { @mkdir( UPLOAD_PATH, 0755, true ); }
				}
				try {
					$pathFile = UPLOAD_PATH . 'qrcode-' . uniqid() .'.png';
					QRcode::png($response['qrcode'], $pathFile, 'h', 3, 5, false );
					$img = file_get_contents($pathFile); 
					// Encode the image string data into base64 
					$img64 = base64_encode($img); 
					$_POST['qrcode'] = $img64; // usado no retorno Ajax
					// Anexa o arquivo via filtro do CF7 (compatível com versões recentes)
					add_filter('wpcf7_mail_components', function($components) use ($pathFile){
						if ( isset($components['attachments']) && is_array($components['attachments']) ) {
							$components['attachments'][] = $pathFile;
						} else {
							$components['attachments'] = array($pathFile);
						}
						return $components;
					});
				} catch (\Throwable $e) {
					logFile('Erro ao gerar/anexar QRCode: ' . $e->getMessage());
				}
				
			}
		}

}

function consume_api($params){
    // Nunca deixe token hardcoded no código. Leia somente das opções do WP.
    $token = get_option( 'abgsoft_autopark_api_token' );
    if (empty($token)) {
        logFile('Token da API ausente. Configure em Configurações → Unimed Promoção.');
        return new \WP_Error('missing_api_token', 'Token da API não configurado.');
    }
    $url = "https://sys.autopark.com.br/api/v1/vouchers";
    $headers = array(
        'Content-Type' => 'application/json',
        'X-Requested-With' => 'XMLHttpRequest'
        );
	$params['api_token'] = $token;
	    //$params = http_build_query($params);
	$params = json_encode($params, JSON_UNESCAPED_UNICODE);
	$args = array(
		'timeout'     => 30,
		'headers' => $headers,
		'body' => $params
	);
	/*ob_start();
	var_dump($args);
	logFile(basename(__FILE__) . "\n" . ob_get_clean());*/
	//logFile("Resposta da requisição: ");
	$response = wp_remote_post($url, $args);
	if ( is_wp_error( $response ) ) {
	   $error_message = $response->get_error_message();
	   //echo "Something went wrong: $error_message";
	   ob_start();
		var_dump($error_message);
		logFile(basename(__FILE__) . "\n" . ob_get_clean());
	}
	$body = wp_remote_retrieve_body($response);
	/*ob_start();
	var_dump($body);
	logFile(basename(__FILE__) . "\n" . ob_get_clean());*/
	
	$data = ( ! is_wp_error( $response ) ) ? json_decode( $body, true ) : null;
	if(gettype($data) === "array" && count($data) === 1){ // 2 json decode - response in text 'array'!?!?
		$data = json_decode( $data[0], true );
	}
	return $data;
}

// ===== Admin Settings =====
function abgsoft_autopark_add_admin_menu() {
	add_options_page(
		'Unimed Promoção',
		'Unimed Promoção',
		'manage_options',
		'abgsoft-autopark-unimed',
		'abgsoft_autopark_options_page'
	);
}

function abgsoft_autopark_settings_init() {
	// Register settings
	register_setting( 'abgsoft_autopark_settings', 'abgsoft_autopark_api_token' );
	register_setting( 'abgsoft_autopark_settings', 'abgsoft_autopark_cf7_form_id' );
	register_setting( 'abgsoft_autopark_settings', 'abgsoft_autopark_promotion_id' );

	add_settings_section(
		'abgsoft_autopark_section_main',
		'Configurações do Unimed Promoção',
		function(){ echo '<p>Defina abaixo as credenciais e IDs utilizados pelo plugin.</p>'; },
		'abgsoft-autopark-unimed'
	);

	add_settings_field(
		'abgsoft_autopark_api_token_field',
		'Token da API',
		function(){
			$value = esc_attr( get_option('abgsoft_autopark_api_token', '') );
			echo '<input type="text" style="width: 480px;" name="abgsoft_autopark_api_token" value="' . $value . '" placeholder="Insira o token da API" />';
		},
		'abgsoft-autopark-unimed',
		'abgsoft_autopark_section_main'
	);

	add_settings_field(
		'abgsoft_autopark_cf7_form_id_field',
		'ID do Formulário CF7',
		function(){
			$value = esc_attr( get_option('abgsoft_autopark_cf7_form_id', '1703') );
			echo '<input type="text" name="abgsoft_autopark_cf7_form_id" value="' . $value . '" />';
		},
		'abgsoft-autopark-unimed',
		'abgsoft_autopark_section_main'
	);

	add_settings_field(
		'abgsoft_autopark_promotion_id_field',
		'ID da Promoção',
		function(){
			$value = esc_attr( get_option('abgsoft_autopark_promotion_id', '1') );
			echo '<input type="number" min="1" name="abgsoft_autopark_promotion_id" value="' . $value . '" />';
		},
		'abgsoft-autopark-unimed',
		'abgsoft_autopark_section_main'
	);
}

function abgsoft_autopark_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	echo '<div class="wrap">';
	echo '<h1>Unimed Promoção</h1>';
	echo '<form action="options.php" method="post">';
	settings_fields( 'abgsoft_autopark_settings' );
	do_settings_sections( 'abgsoft-autopark-unimed' );
	submit_button();
	echo '</form>';
	echo '</div>';
}

?>