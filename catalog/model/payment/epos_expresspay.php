<?php
class ModelPaymentEposExpressPay extends Model {
    const NAME_PAYMENT_METHOD                       = 'epos_expresspay_name_payment_method';
    const SORT_ORDER_PARAM_NAME                     = 'epos_expresspay_sort_order';

	public function getMethod($address, $total) {
		$this->load->language('payment/epos_expresspay');
		
		$status = true;

        if ($total > 0) {
            $status = true;
        }
		
        $method_data = array();

        $code = 'epos_expresspay';
        
        // Название метода оплаты
        $textTitle = $this->language->get('heading_title');
        if($this->config->get(self::NAME_PAYMENT_METHOD) !== null){
            $textTitle = $this->config->get(self::NAME_PAYMENT_METHOD);
        }
        
        $sortOrder = $this->config->get(self::SORT_ORDER_PARAM_NAME);

		if ($status) {
			$method_data = array(
				'code'       => $code,
				'title'      => $textTitle,
				'terms'      => '',
				'sort_order' => $sortOrder
			);
		}
		
		return $method_data;
	}
}
?>