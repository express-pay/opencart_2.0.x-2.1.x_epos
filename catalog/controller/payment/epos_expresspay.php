<?php
class ControllerPaymentEposExpressPay extends Controller {
    const TOKEN_PARAM_NAME                          = 'epos_expresspay_token';
    const SERVICE_ID_PARAM_NAME                     = 'epos_expresspay_service_id';
    const SECRET_WORD_PARAM_NAME                    = 'epos_expresspay_secret_word';
    const USE_SIGNATURE_FOR_NOTIFICATION_PARAM_NAME = 'epos_expresspay_is_use_signature_for_notification';
    const SECRET_WORD_NOTIFICATION_PARAM_NAME       = 'epos_expresspay_secret_word_for_notification';
    const NOTIFICATION_URL_PARAM_NAME               = 'epos_expresspay_url_notification';
    const IS_SHOW_QR_CODE_PARAM_NAME                = 'epos_expresspay_is_show_qr_code';
    const IS_NAME_EDIT_PARAM_NAME                   = 'epos_expresspay_is_name_editable';
    const IS_AMOUNT_EDIT_PARAM_NAME                 = 'epos_expresspay_is_amount_editable';
    const IS_ADDRESS_EDIT_PARAM_NAME                = 'epos_expresspay_is_address_editable';
    const SERVICE_PROVIDER_ID_PARAM_NAME            = 'epos_expresspay_service_provider_id';
    const EPOS_SERVICE_ID_PARAM_NAME                = 'epos_expresspay_epos_service_id';
    const IS_TEST_MODE_PARAM_NAME                   = 'epos_expresspay_is_test_mode';
    const API_URL_PARAM_NAME                        = 'epos_expresspay_api_url';
    const SANDBOX_URL_PARAM_NAME                    = 'epos_expresspay_sandbox_url';
    const INFO_PARAM_NAME                           = 'epos_expresspay_info';
    const MESSAGE_SUCCESS_PARAM_NAME                = 'epos_expresspay_message_success';
    const PROCESSED_STATUS_ID_PARAM_NAME            = 'epos_expresspay_processed_status_id';
    const SUCCESS_STATUS_ID_PARAM_NAME              = 'epos_expresspay_success_status_id';
    const FAIL_STATUS_ID_PARAM_NAME                 = 'epos_expresspay_fail_status_id';


	public function index() {
		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['text_loading'] = $this->language->get('text_loading');

		$this->load->model('checkout/order');
		$orderId = $this->session->data['order_id'];
		$order_info = $this->model_checkout_order->getOrder($orderId);
        $amount = $this->currency->format($order_info['total'], $this->session->data['currency'], '', false);
        $amount = str_replace('.',',',$amount);

		$secret_word = $this->config->get(self::SECRET_WORD_PARAM_NAME);

        //Обрезать +
        //заменить знаки (' ', '-','(',')') на пустую строку
        //посчитать количество символов
        //Если номер не прошел проверку, не использовать
        $smsPhone = $order_info['telephone'];

        $smsPhone = str_replace('+', '', $smsPhone);
        $smsPhone = str_replace(' ', '', $smsPhone);
        $smsPhone = str_replace('-', '', $smsPhone);
        $smsPhone = str_replace('(', '', $smsPhone);
        $smsPhone = str_replace(')', '', $smsPhone);

        $signatureParams['token'] = $this->config->get(self::TOKEN_PARAM_NAME);
        $signatureParams['serviceId'] = $this->config->get(self::SERVICE_ID_PARAM_NAME);
        $signatureParams['accountNo'] = $orderId;
        $signatureParams['amount'] = $amount;
        $signatureParams['currency'] = 933;
        $signatureParams['info'] = str_replace('##order_id##', $orderId, $this->config->get(self::INFO_PARAM_NAME));
        $signatureParams['surname'] = mb_strimwidth($order_info['payment_lastname'], 0, 30);
        $signatureParams['firstName'] = mb_strimwidth($order_info['payment_firstname'], 0, 30);
        $signatureParams['city'] = mb_strimwidth($order_info['payment_city'], 0, 30);
        $signatureParams['isNameEditable'] =  ( $this->config->get(self::IS_NAME_EDIT_PARAM_NAME) == 'on' ) ? 1 : 0;
        $signatureParams['isAmountEditable'] = ( $this->config->get(self::IS_AMOUNT_EDIT_PARAM_NAME) == 'on' ) ? 1 : 0;
        $signatureParams['isAddressEditable'] =  ( $this->config->get(self::IS_ADDRESS_EDIT_PARAM_NAME) == 'on' ) ? 1 : 0;
        $signatureParams['emailNotification'] = $order_info['email'];
        $signatureParams['smsPhone'] = $smsPhone;
        $signatureParams['returnType'] = 'redirect';
        $signatureParams['returnUrl'] = $this->url->link('payment/epos_expresspay/success');
        $signatureParams['failUrl'] = $this->url->link('payment/epos_expresspay/fail');
        $signatureParams["returnInvoiceUrl"] = "1";

        $data['signature'] = self::computeSignature($signatureParams, $secret_word, 'add-web-invoice');
        unset($signatureParams['token']);
        $data = array_merge($data, $signatureParams);
		$url = ( $this->config->get(self::IS_TEST_MODE_PARAM_NAME) != 'on' ) ? $this->config->get(self::API_URL_PARAM_NAME) : $this->config->get(self::SANDBOX_URL_PARAM_NAME);
		$data['action'] = $url.'v1/web_invoices';

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/epos_expresspay.tpl')) {
			return $this->load->view($this->config->get('config_template') . '/template/payment/epos_expresspay.tpl', $data);
		} else {
			return $this->load->view('default/template/payment/epos_expresspay.tpl', $data);
		}
	}
	
	public function success() {
        $orderId = $this->session->data['order_id'];
		$this->log_info('success', 'Initialization render success page; ORDER ID - ' . $orderId);
		$this->cart->clear();
		$this->load->language('payment/epos_expresspay');
		$headingTitle = $this->language->get('heading_title_success');
		$this->document->setTitle($headingTitle);
		$data['heading_title'] = $headingTitle;
        $textMessage = $this->config->get(self::MESSAGE_SUCCESS_PARAM_NAME);
        if (empty($textMessage)) {
            $textMessage = $this->language->get('text_message_success');
        }
        $data['text_message'] = nl2br(str_replace('##order_id##', $orderId, $textMessage));
        $eripPath = $this->language->get('erip_path');
        $data['content_body'] = str_replace('##erip_path##', $eripPath, $this->language->get('content_success'));

        $eposCode = $this->config->get(self::SERVICE_PROVIDER_ID_PARAM_NAME).'-'.$this->config->get(self::EPOS_SERVICE_ID_PARAM_NAME).'-'.$orderId;
        $data['content_body'] = nl2br(str_replace('##order_id##', $eposCode, $data['content_body']));
        $data['qr_description'] = $this->language->get('qr_description');

		$data['test_mode_label'] = $this->language->get('test_mode_label');
		$data['text_send_notify_success'] = $this->language->get('text_send_notify_success');
		$data['text_send_notify_cancel'] = $this->language->get('text_send_notify_cancel');
		$data['test_mode'] = ( $this->config->get(self::IS_TEST_MODE_PARAM_NAME) == 'on' ) ? true : false;
		$data['order_id'] = $this->session->data['order_id'];
		$data['signature_success'] = $data['signature_cancel'] = "";

		$secret_word = $this->config->get(self::SECRET_WORD_PARAM_NAME);
        $data['signature_success'] = $this->compute_signature('{"CmdType": 1, "AccountNo": ' . $orderId . '}', $secret_word);
        $data['signature_cancel'] = $this->compute_signature('{"CmdType": 2, "AccountNo": ' . $orderId . '}', $secret_word);

        if ($this->config->get(self::IS_SHOW_QR_CODE_PARAM_NAME)  == 'on' && isset($this->request->get['ExpressPayInvoiceNo'])) {
            $invoiceNo = $this->request->get['ExpressPayInvoiceNo'];
            try {
				$apiUrl = ( $this->config->get(self::IS_TEST_MODE_PARAM_NAME) != 'on' ) ? $this->config->get(self::API_URL_PARAM_NAME) : $this->config->get(self::SANDBOX_URL_PARAM_NAME);
				$secret_word = $this->config->get(self::SECRET_WORD_PARAM_NAME);
				$signatureParams = array(
					"Token" => $this->config->get(self::TOKEN_PARAM_NAME),
					"InvoiceId" => $invoiceNo,
					"ViewType" => "base64",
					"ImageWidth" => "",
					"ImageHeight" => ""
				);
				$signatureParams['Signature'] = self::computeSignature($signatureParams, $secret_word, 'get-qr-code');

				$qrbase64json = self::sendRequest($apiUrl ."v1/qrcode/getqrcode?". http_build_query($signatureParams));
                $qrbase64 = json_decode($qrbase64json);
                if (isset($qrbase64->QrCodeBody))
                {
                    $data['qr_code'] = $qrbase64->QrCodeBody;
                    $data['show_qr_code'] = 1;
                }
            } catch (Exception $e) {
				$this->log_error_exception('success', 'Get response; INVOICE ID - ' . $invoiceNo. '; RESPONSE - ' . $qrbase64json, $e);
            }
        }

		$this->load->model('checkout/order');
		$this->model_checkout_order->addOrderHistory($orderId, $this->config->get(self::PROCESSED_STATUS_ID_PARAM_NAME));

        $data = $this->setBreadcrumbs($data);
        $data = $this->setButtons($data);
        $data = $this->setController($data);
        $data['continue'] = $this->url->link('common/home');

		$this->log_info('success', 'End render success page; ORDER ID - ' . $orderId);

		$this->response->setOutput($this->load->view('default/template/payment/epos_expresspay_success.tpl', $data));
	}
	
	public function fail() {
        $orderId = $this->session->data['order_id'];
		$this->log_info('fail', 'Initialization render fail page; ORDER ID - ' . $orderId);
		$this->load->language('payment/epos_expresspay');
		$headingTitle  = $this->language->get('heading_title_fail');
		$this->document->setTitle($headingTitle);
        $data['heading_title'] = $headingTitle;
		$data['text_message'] = nl2br(str_replace('##order_id##', $orderId, $this->language->get('text_message_fail')));

		$this->load->model('checkout/order');
		$this->model_checkout_order->addOrderHistory($orderId, $this->config->get(self::FAIL_STATUS_ID_PARAM_NAME));

        $data = $this->setBreadcrumbs($data);
        $data = $this->setButtons($data);
        $data = $this->setController($data);
        $data['continue'] = $this->url->link('checkout/checkout');

		$this->log_info('fail', 'End render fail page; ORDER ID - ' . $orderId);

		$this->response->setOutput($this->load->view('default/template/payment/epos_expresspay_failure.tpl', $data));
	}
	
    private function setBreadcrumbs($data)
    {
		$data['breadcrumbs'] = array(); 

		$data['breadcrumbs'][] = array(
			'href'      => $this->url->link('common/home'),
			'text'      => $this->language->get('text_home'),
			'separator' => false
		);

		$data['breadcrumbs'][] = array(
			'href'      => $this->url->link('checkout/cart'),
			'text'      => $this->language->get('text_basket'),
			'separator' => $this->language->get('text_separator')
		);

		$data['breadcrumbs'][] = array(
			'href'      => $this->url->link('checkout/checkout', '', 'SSL'),
			'text'      => $this->language->get('text_checkout'),
			'separator' => $this->language->get('text_separator')
		);

        return $data;
    }

    private function setButtons($data)
    {
        $data['button_continue'] = $this->language->get('button_continue');
        $data['text_loading'] = $this->language->get('text_loading');
        $data['continue'] = $this->url->link('checkout/checkout');

        return $data;
    }

    private function setController($data)
    {
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        return $data;
    }

	public function notify() {
		$this->log_info('notify', 'Get notify from server; REQUEST METHOD - ' . $_SERVER['REQUEST_METHOD']);

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $useSignatureForNotification = ($this->config->get(self::USE_SIGNATURE_FOR_NOTIFICATION_PARAM_NAME) == 'on') ? true : false;
            $dataJSON = (isset($this->request->post['Data'])) ? htmlspecialchars_decode($this->request->post['Data']) : '';
            $signature = (isset($this->request->post['Signature'])) ? $this->request->post['Signature'] : '';
		    
		    if($useSignatureForNotification) {
                $secretWordForNotification = $this->config->get(self::SECRET_WORD_NOTIFICATION_PARAM_NAME);

                $valid_signature = self::computeSignature(array("data" => $dataJSON), $secretWordForNotification, 'notification');
		    	if($valid_signature == $signature)
			        $this->notify_success($dataJSON);
			    else  {
					$this->log_error('notify_fail', "Fail to update status; RESPONSE - " . $dataJSON);

					header("HTTP/1.0 400 Bad Request");
					echo 'FAILED | Incorrect digital signature';
				}
		    } else 
		    	$this->notify_success($dataJSON);
		}
		$this->log_info('notify', 'End (Get notify from server); REQUEST METHOD - ' . $_SERVER['REQUEST_METHOD']);
	}

	private function notify_success($dataJSON) {
        // Преобразование из json в array
        $data = array();
		try {
        	$data = json_decode($dataJSON);
    	} catch(Exception $e) {
            header('HTTP/1.1 400 Bad Request');
            echo 'FAILED | Failed to decode data';
    		$this->log_error('notify_fail', "Fail to parse the server response; RESPONSE - " . $dataJSON);
			return;
    	}

		$this->load->model('checkout/order');

        if(isset($data->CmdType)) {
        	switch ($data->CmdType) {
        		case '1':
					if($this->model_checkout_order->getOrder($data->AccountNo)['order_status_id'] != $this->config->get(self::SUCCESS_STATUS_ID_PARAM_NAME)){
        				$this->model_checkout_order->addOrderHistory($data->AccountNo, $this->config->get(self::SUCCESS_STATUS_ID_PARAM_NAME));
        				$this->log_info('notify_success', 'Initialization to update status. STATUS ID - ' . $this->config->get(self::SUCCESS_STATUS_ID_PARAM_NAME) . "; RESPONSE - " . $dataJSON);
					}
        			break;
        		case '2':
        			$this->model_checkout_order->addOrderHistory($data->AccountNo, $this->config->get(self::FAIL_STATUS_ID_PARAM_NAME));
					$this->log_info('notify_success', 'Initialization to update status. STATUS ID - ' . $this->config->get(self::FAIL_STATUS_ID_PARAM_NAME) . "; RESPONSE - " . $dataJSON);

        			break;
				case 3:
					if(isset($data->Status)){
						switch($data->Status){
							case 1: // Ожидает оплату
								if($this->model_checkout_order->getOrder($data->AccountNo)['order_status_id'] != $this->config->get(self::SUCCESS_STATUS_ID_PARAM_NAME)){
									$this->model_checkout_order->addOrderHistory($data->AccountNo, $this->config->get(self::PROCESSED_STATUS_ID_PARAM_NAME));
									$this->log_info('notify_success', 'Initialization to update status. STATUS ID - ' . $this->config->get(self::SUCCESS_STATUS_ID_PARAM_NAME) . "; RESPONSE - " . $dataJSON);
								}
								break;
							case 2: // Просрочен
								$this->model_checkout_order->addOrderHistory($data->AccountNo, $this->config->get(self::FAIL_STATUS_ID_PARAM_NAME));
								$this->log_info('notify_success', 'Initialization to update status. STATUS ID - ' . $this->config->get(self::SUCCESS_STATUS_ID_PARAM_NAME) . "; RESPONSE - " . $dataJSON);
								break;
							case 3: // Оплачен
							case 6: // Оплачен с помощью банковской карты
								if($this->model_checkout_order->getOrder($data->AccountNo)['order_status_id'] != $this->config->get(self::SUCCESS_STATUS_ID_PARAM_NAME)){
                                    $this->model_checkout_order->addOrderHistory($data->AccountNo, $this->config->get(self::SUCCESS_STATUS_ID_PARAM_NAME));
                                    $this->log_info('notify_success', 'Initialization to update status. STATUS ID - ' . $this->config->get(self::SUCCESS_STATUS_ID_PARAM_NAME) . "; RESPONSE - " . $dataJSON);
                                    
                                }
                                break;
							case 5: // Отменен
								$this->model_checkout_order->addOrderHistory($data->AccountNo, $this->config->get(self::FAIL_STATUS_ID_PARAM_NAME));
								$this->log_info('notify_success', 'Initialization to update status. STATUS ID - ' . $this->config->get(self::SUCCESS_STATUS_ID_PARAM_NAME) . "; RESPONSE - " . $dataJSON);
								break;
						}
					}
					break;
        	}
			header("HTTP/1.1 200 OK");
			echo 'OK | the notice is processed';
			$this->log_info("notify_success", "the notice is processed");
			return;
        } 

        header('HTTP/1.1 400 Bad Request');
        echo 'FAILED | The notice is not processed';
		$this->log_error('notify_fail', "Fail to parse the server response; RESPONSE - " . $dataJSON);
	}

	private function compute_signature($json, $secret_word) {
	    $hash = NULL;
	    $secret_word = trim($secret_word);
	    
	    if(empty($secret_word))
			$hash = strtoupper(hash_hmac('sha1', $json, ""));
	    else
	        $hash = strtoupper(hash_hmac('sha1', $json, $secret_word));

	    return $hash;
	}
	
    private function sendRequest($url) 
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    /**
     * 
     * Формирование цифровой подписи
     * 
     * @param array  $signatureParams Список передаваемых параметров
     * @param string $secretWord      Секретное слово
     * @param string $method          Метод формирования цифровой подписи
     * 
     * @return string $hash           Сформированная цифровая подпись
     * 
     */
    private static function computeSignature($signatureParams, $secretWord, $method)
    {
        $normalizedParams = array_change_key_case($signatureParams, CASE_LOWER);
        $mapping = array(
            "get-qr-code"          => array(
                "token",
                "invoiceid",
                "viewtype",
                "imagewidth",
                "imageheight"
            ),
            "add-web-invoice"      => array(
                "token",
                "serviceid",
                "accountno",
                "amount",
                "currency",
                "expiration",
                "info",
                "surname",
                "firstname",
                "patronymic",
                "city",
                "street",
                "house",
                "building",
                "apartment",
                "isnameeditable",
                "isaddresseditable",
                "isamounteditable",
                "emailnotification",
                "smsphone",
                "returntype",
                "returnurl",
                "failurl",
                "returninvoiceurl"
            ),
            "add-webcard-invoice" => array(
                "token",
                "serviceid",
                "accountno",
                "expiration",
                "amount",
                "currency",
                "info",
                "returnurl",
                "failurl",
                "language",
                "sessiontimeoutsecs",
                "expirationdate",
                "returntype",
                "returninvoiceurl"
			),
            "notification"         => array(
                "data"
            )
        );
        $apiMethod = $mapping[$method];
        $result = "";
        foreach ($apiMethod as $item) {
            $result .= (isset($normalizedParams[$item])) ? $normalizedParams[$item] : '';
        }
        $hash = strtoupper(hash_hmac('sha1', $result, $secretWord));
        return $hash;
    }

    private function log_error_exception($name, $message, $e) {
    	$this->log($name, "ERROR" , $message . '; EXCEPTION MESSAGE - ' . $e->getMessage() . '; EXCEPTION TRACE - ' . $e->getTraceAsString());
    }

    private function log_error($name, $message) {
    	$this->log($name, "ERROR" , $message);
    }

    private function log_info($name, $message) {
    	$this->log($name, "INFO" , $message);
    }

    private function log($name, $type, $message) {
    	$log = new Log('epos_expresspay/express-pay-' . date('Y.m.d') . '.log');
    	$log->write($type . " - IP - " . $_SERVER['REMOTE_ADDR'] . "; USER AGENT - " . $_SERVER['HTTP_USER_AGENT'] . "; FUNCTION - " . $name . "; MESSAGE - " . $message . ';');
    }
}

?>