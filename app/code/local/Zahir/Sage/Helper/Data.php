<?php

class Zahir_Sage_Helper_Data extends Mage_Core_Helper_Abstract
{
    private $_storeId = 0;
    const GuestAccountRef = "Guest";
    private $_logfile = "sage_integration.log";
    private $_forceLog = true;
    private $_tradeGroupId = 16;
    const TradeAccount = "3TRADE01";
    const GbpAccount = "3WEBSI01";
    const EurAccount = "3WEBSI03";
    const DkkAccount = "3WEBSI04";
    const SkAccount = "3WEBSI05";
    const UsdAccount = "3WEBSI06";

    public function __construct()
    {
        $this->_storeId = Mage::app()->getStore()->getStoreId();
    }

    public function getGeneralConfig($key)
    {
        return Mage::getStoreConfig('zahir_sage/general/' . $key, $this->_storeId);
    }

    public function getDebugConfig($key)
    {
        return Mage::getStoreConfig('zahir_sage/debug/' . $key, $this->_storeId);
    }

    public function getStockConfig($key)
    {
        return Mage::getStoreConfig('zahir_sage/stock/' . $key, $this->_storeId);
    }

    public function isHolded($order)
    {
        if ($order->getState() === Mage_Sales_Model_Order::STATE_HOLDED) {
            return "true";
        } else {
            return "false";
        }

    }

    public function getOrders($status, $time)
    {
        $orders = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter('created_at', array('from' => $time))
            ->addAttributeToFilter('is_sage_exported', array('eq' => 0))
            ->addAttributeToFilter('status', array('eq' => $status));
        return $orders;
    }

    public function getIdFromSku($sku)
    {
        $coreResource = Mage::getSingleton('core/resource');
        $connect = $coreResource->getConnection('core_write');

        $entity_row = $connect->query("SELECT entity_id FROM catalog_product_entity p_e WHERE p_e.sku = '$sku'")->fetchObject();
        if ($entity_row):$entity_id = $entity_row->entity_id;
        else:
            $entity_id = false;
        endif;
        return $entity_id;
    }

    public function getCustomerAccount($order)
    {
        $currency = $order->getOrderCurrencyCode();
        $group = $order->getCustomerGroupId();
        if ($group == $this->_tradeGroupId) {
            return self::TradeAccount;
        } else {
            switch ($currency) :
                case "GBP":
                    return self::GbpAccount;
                case "EUR":
                    return self::EurAccount;
                case "DKK":
                    return self::DkkAccount;
                case "SEK":
                    return self::SkAccount;
                case "USD":
                    return self::UsdAccount;
                default :
                    return self::GbpAccount;
            endswitch;

        }
        return self::GbpAccount;

    }

    public function logError($a, $b)
    {
        Mage::log($a, null, $this->_logfile, $this->_forceLog);
        Mage::log($b, null, $this->_logfile, $this->_forceLog);

        if ($this->getDebugConfig("status")) {
            if ($email = $this->getDebugConfig("email")) {
                $subject = $a;
                $body = $b;
                $this->notify($email, $email, $subject, $body);
            }
        }
    }

    private function notify($to, $toname, $subject, $html_body)
    {
        try {
            $mail = new Zend_Mail();
            $mail->setType(Zend_Mime::MULTIPART_RELATED);
            $mail->setBodyHtml($html_body);
            $mail->setFrom('sandorm@theproteinworks.com', 'Sage Integration Automated');
            $mail->addTo($to, $toname);
            $mail->setSubject($subject);

            $mail->send();
        } catch (Exception $e) {
            Mage::log("\t\t EMAIL SENDING ERROR: " . $to . " - " . $toname . " - " . $subject . " - " . $html_body . " | " . $e->getMessage(), null, $this->_logfile, $this->_forceLog);
        }
    }

    public function getDeliveryAddressBlock($order)
    {
        $xml = null;
        try {
            $xml = null;
            $address = $order->getShippingAddress();

            $xml .= '<PostalName>' . htmlspecialchars($address->getName(), ENT_XML1, 'UTF-8') . '</PostalName>';
            $xml .= '<AddressLine1>' . htmlspecialchars(substr($address->getStreet()[0], 0, 59), ENT_XML1, 'UTF-8') . '</AddressLine1>';
            if (isset($address->getStreet()[1])) : $xml .= '<AddressLine2>' . htmlspecialchars(substr($address->getStreet()[1],0,59), ENT_XML1, 'UTF-8') . '</AddressLine2>'; endif;
            $xml .= '<PostCode>' . htmlspecialchars(substr($address->getPostcode(), 0, 9), ENT_XML1, 'UTF-8') . '</PostCode>';
            $xml .= '<City>' . htmlspecialchars(substr($address->getCity(), 0, 59), ENT_XML1, 'UTF-8') . '</City>';
            $xml .= '<County>' . htmlspecialchars($address->getRegion(), ENT_XML1, 'UTF-8') . '</County>';
            $xml .= '<Country>' . htmlspecialchars($address->getCountry(), ENT_XML1, 'UTF-8') . '</Country>';
            $xml .= '<CountryCode>' . $address->getCountryId() . '</CountryCode>';

            return $xml;
        } catch (Exception $e) {
            /* Add in error */
            return $xml;
        }

    }

    public function getCustomerAddressBlock($order)
    {
        $xml = null;
        try {
            $xml = null;
            $address = $order->getBillingAddress();

            $xml .= '<AddressLine1>' . htmlspecialchars(substr($address->getStreet()[0], 0, 59), ENT_XML1, 'UTF-8') . '</AddressLine1>';
            if (isset($address->getStreet()[1])) : $xml .= '<AddressLine2>' . htmlspecialchars(substr($address->getStreet()[1],0,59), ENT_XML1, 'UTF-8') . '</AddressLine2>'; endif;
            $xml .= '<PostCode>' . htmlspecialchars(substr($address->getPostcode(), 0, 9), ENT_XML1, 'UTF-8') . '</PostCode>';
            $xml .= '<City>' . htmlspecialchars(substr($address->getCity(), 0, 59), ENT_XML1, 'UTF-8') . '</City>';
            $xml .= '<County>' . $address->getRegion() . '</County>';

            return $xml;
        } catch (Exception $e) {
            /* Add in error */
            return $xml;
        }

    }

    public function getCustomerPhone($order)
    {
        $phone="";
        try {
            $address = $order->getShippingAddress();
            $phone=$address->getTelephone();
            if (strlen($phone) < 5) {
                $address = $order->getBillingAddress();
                $phone=$address->getTelephone();
            }
            return $phone;
        } catch (Exception $e) {
            /* Add in error */
            return $phone;
        }

    }

    public function getItemsBlock($order)
    {
        $xml = null;
        $items = $order->getAllItems();
        $bundlechecks = array();
        $prmodel = Mage::getModel("catalog/product");
        foreach ($items as $item) {
            if ($parentid = $item->getParentItemId()) {
                $product = $prmodel->load($item->getProductId());
                $finalprice = $product->getFinalPrice();
                $bundlechecks[$parentid][$item->getProductId()] = $finalprice;
                $bundlechecks[$parentid]['total'] += $finalprice;

                $product->clearInstance();
            }
        }
        $bundleprice = array();
        $bundleskus = array();
        $configprice = array();
        $configskus = array();
        foreach ($items as $item) {
            $discountprice = 0;
            if ($item->getProductType() == "bundle") {
                $bundleprice[$item->getItemId()] = $item->getPrice();
                $bundleskus[$item->getItemId()] = $item->getSku();
                $bundlediscount[$item->getItemId()] = $item->getDiscountAmount();
                continue;
            } else if ($item->getProductType() == "configurable") {
                $configprice[$item->getItemId()] = $item->getPrice();
                $configskus[$item->getItemId()] = $item->getSku();
                $configdiscount[$item->getItemId()] = $item->getDiscountAmount();
                continue;
            }

            $sku = $item->getSku();
            if (strpos($sku, "CYO") === 0) {
                $sku = "CYO";
            }
            $xml .= '<Item>';
            $xml .= '<ItemCode>' . substr($sku, 0, 29) . '</ItemCode>';
            $xml .= '<ItemDescription>' . htmlspecialchars($item->getName(), ENT_XML1, 'UTF-8') . '</ItemDescription>';
            $xml .= '<LineQuantity>' . $item->getQtyOrdered() . '</LineQuantity>';
            $itemid=$item->getId();
            if ($parentid = $item->getParentItemId()) {
                if (isset($configprice[$parentid])) { /* It's a config item */
                    $calculated = round($configprice[$parentid], 5);
                    $xml .= '<UnitPrice>' . $calculated . '</UnitPrice>';
                    $itemid=$parentid;
                    if (isset($configdiscount[$parentid])) {
                        $cdiscount = $configdiscount[$parentid];
                        if ($cdiscount > 0) {
                            $discountprice = round($cdiscount, 5);
                        }
                    }
                } else { /* It's  abundle item */
                    $itemprice = $bundlechecks[$parentid][$item->getProductId()];
                    $bundlepriceforcalc = $bundleprice[$parentid];
                    $otalitemprice = $bundlechecks[$parentid]['total'];
                    $calculated = round(($itemprice * $bundlepriceforcalc) / $otalitemprice, 5);
                    $xml .= '<UnitPrice>' . $calculated . '</UnitPrice>';
                    if (isset($bundlediscount[$parentid])) {
                        $bdiscount = $bundlediscount[$parentid];
                        if ($bdiscount > 0) {
                            $discountprice = round(($calculated / $bundlepriceforcalc) * $bdiscount, 5);
                        }
                    }
                }

            } else {
                $xml .= '<UnitPrice>' . $item->getPrice() . '</UnitPrice>';
            }
            if ($item->getDiscountAmount() > 0) {
                $discountprice = round($item->getDiscountAmount() / $item->getQtyOrdered(), 5);
            }
            $xml .= '<UnitDiscountValue>' . $discountprice . '</UnitDiscountValue>';
            $xml .= '<AnalysisCodes>';
            $xml .= '<AnalysisCode7>' . $itemid . '</AnalysisCode7>';
            if ($parentid = $item->getParentItemId()) {
                $xml .= '<AnalysisCode12>' . $bundleskus[$parentid] . '</AnalysisCode12>';
            }
            $xml .= '</AnalysisCodes>';
            $xml .= '</Item>';
        }

        return $xml;

    }

    private function sanitizeShipping($data)
    {
        $sanitizeddata = $data;
        $position = strpos($data, "-");
        $sanitizeddata = substr($data, $position + 1, 60);
        return $sanitizeddata;

    }

    public function getOrderAnalysisBlock($order)
    {
        $xml = null;

        $xml .= '<AnalysisCode1>' . $order->getStoreId() . '</AnalysisCode1>';
        $xml .= '<AnalysisCode2>' . $this->getOrderChanel($order) . '</AnalysisCode2>';
        $xml .= '<AnalysisCode3>' . $order->getClickCollectId() . '</AnalysisCode3>';
        $xml .= '<AnalysisCode4>' . $order->getClickCollectEnabled() . '</AnalysisCode4>';
        $xml .= '<AnalysisCode5>' . $order->getOrderCurrencyCode() . '</AnalysisCode5>';
        $xml .= '<AnalysisCode6>' . $order->getShippingMethod() . '</AnalysisCode6>';
        $xml .= '<AnalysisCode10>' . $this->sanitizeShipping($order->getShippingDescription()) . '</AnalysisCode10>';
        $xml .= '<AnalysisCode11>' . $order->getCustomerId() . '</AnalysisCode11>';
        $xml .= '<AnalysisCode13>' . substr($order->getCouponCode(), 0, 39) . '</AnalysisCode13>';


        return $xml;

    }

    private function getOrderChanel($order)
    {
        try {
            $coreResource = Mage::getSingleton('core/resource');
            $connect = $coreResource->getConnection('core_write');
            $chanel = $connect->query("SELECT b.url,b.component_mode FROM m2epro_order as a JOIN m2epro_marketplace as b ON a.marketplace_id=b.id
        where magento_order_id=" . $order->getId() . ";")
                ->fetchObject();

            if ($chanel) {
                return $chanel->component_mode;
            } else {
                return 'web';
            }
        } catch (Exception $e) {
            Mage::log("\t\t Order Chanel Error" . $e->getMessage(), null, $this->_logfile, $this->_forceLog);
            return 'web';
        }
    }

    public function getFileNameAndPath($order)
    {
        $filename = $order->getIncrementId() . "-" . date("Y-m-d") . ".xml";
        $fullpath = $this->getGeneralConfig("path");
        if (is_writable($fullpath)) {

        } else {
            Mage::throwException('Not writable:' . $fullpath);
        }
        return $fullpath . $filename;
    }

    public function stockReindex()
    {
        $indexnumbers = array(4, 8);
        foreach ($indexnumbers as $index) {
            try {
                $process = Mage::getModel('index/process')->load($index);
                $process->reindexAll();
            } catch (Exception $e) {
                Mage::log("\t\t Index error ERROR:  " . $e->getMessage(), null, $this->_logfile, $this->_forceLog);
            }
        }

        $type = 'block_html';
        Mage::app()->getCacheInstance()->cleanType($type);
        /*$type = 'fpc';
        Mage::app()->getCacheInstance()->cleanType($type);*/
        return true;
    }


}
