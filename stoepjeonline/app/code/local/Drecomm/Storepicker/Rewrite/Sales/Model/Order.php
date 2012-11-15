<?php

class Drecomm_Storepicker_Rewrite_Sales_Model_Order extends Mage_Sales_Model_Order {
    protected function _getEmails($configPath) {

        $bccEmail = Mage::getStoreConfig($configPath, $this->getStoreId());

        if (!empty($bccEmail)) {
            Mage::log('BCCemail: '.$bccEmail);
            return explode(',', $bccEmail);
        }

        return false;
    }

    public function _getStoreOwnerEmail() {
        $extra = Mage::app()->getRequest()->getParam('extra1');

        if (!empty($extra)) {
            $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
            $select = $connection->select()->from('storepicker_stores',array('id'))->where('customerno = ?',$extra);

            $storeId = $connection->fetchRow($select);
            $storeId = $storeId['id'];
        }

        if (empty($storeId)) {
            $storeId = Mage::getSingleton('core/session')->getStorepickerId();
        }

        $stores = Mage::getModel('storepicker/store');
        $stores->load($storeId);

        $email = $stores->getEmail();

        if (!empty($email)) {
            Mage::log('StoreOwner: '.$email);
            return $email;
        }

        return false;
    }

	public function getStorepickerInfo($func) {
		$storeId = Mage::app()->getRequest()->getParam('extra1');

		if (empty($storeId)) {
			$storeId = Mage::getSingleton('core/session')->getStorepickerId();
		}

		$stores = Mage::getModel('storepicker/store');
		$stores->load($storeId);

		$now = Mage::getModel('core/date')->timestamp(time());

		if (date('G', $time) <= 10) {
			$time = $now + 86400;
		} else {
			$time = $now + 86400*2;
		}
		$daysOfWeekTranslated = array(
			"sunday" => "Zondag",
			"monday" => "Maandag",
			"tuesday" => "Dinsdag",
			"wednesday" => "Woensdag",
			"thursday" => "Donderdag",
			"friday" => "Vrijdag",
			"saturday" => "Zaterdag"
		);

		switch ($func) {
			case 'getOpen':
				$open['sunday'] = $stores->getOpensunday();
				$open['monday'] = $stores->getOpenmonday();
				$open['tuesday'] = $stores->getOpentuesday();
				$open['wednesday'] = $stores->getOpenwednesday();
				$open['thursday'] = $stores->getOpenthursday();
				$open['friday'] = $stores->getOpenfriday();
				$open['saturday'] = $stores->getOpensaturday();

				for ($passedDays = 0; $passedDays < 7; $passedDays ++) {
					$timeCounter = $time + 86400*$passedDays;
					$dayOfWeek = strtolower(date('l', $timeCounter));
					$dowString .= $dayOfWeek ."|";
					if ($open[$dayOfWeek] != '-' && $open[$dayOfWeek] != '') {
						$openTime = date('d-m-Y', $timeCounter) ." {$daysOfWeekTranslated[$dayOfWeek]} {$open[$dayOfWeek]}";
						return $openTime;
					}
				}

				return 'n.v.t.';

				break;

			case 'getSend':
				$send['sunday'] = $stores->getSendsunday();
				$send['monday'] = $stores->getSendmonday();
				$send['tuesday'] = $stores->getSendtuesday();
				$send['wednesday'] = $stores->getSendwednesday();
				$send['thursday'] = $stores->getSendthursday();
				$send['friday'] = $stores->getSendfriday();
				$send['saturday'] = $stores->getSendsaturday();

				for ($passedDays = 0; $passedDays < 7; $passedDays ++) {
					$timeCounter = $time + 86400*$passedDays;
					$dayOfWeek = strtolower(date('l', $timeCounter));
					if ($send[$dayOfWeek] != '-' && $send[$dayOfWeek] != '') {
						$sendTime = date('d-m-Y', $timeCounter) ." {$daysOfWeekTranslated[$dayOfWeek]} {$send[$dayOfWeek]}";
						return $sendTime;
					}
				}

				return 'n.v.t.';

				break;

			default:
				return call_user_func(array($stores, $func));
		}
	}

    /**
     * Send email with order data
     *
     * @return Mage_Sales_Model_Order
     */
    public function sendNewOrderEmail()
    {
        $storeId = $this->getStore()->getId();

        if (!Mage::helper('sales')->canSendNewOrderEmail($storeId)) {
            return $this;
        }
        // Get the destination email addresses to send copies to
        $copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
        $storeOwner = $this->_getStoreOwnerEmail();
        if (self::XML_PATH_EMAIL_COPY_METHOD == 'copy') {
            $copyTo[] = $storeOwner;
            Mage::log('Copy method: copy');
        }
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);

        // Start store emulation process
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($this->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        // Retrieve corresponding email template id and customer name
        if ($this->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
            $customerName = $this->getBillingAddress()->getName();
        } else {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
            $customerName = $this->getCustomerName();
        }

        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($this->getCustomerEmail(), $customerName);
        Mage::log('Customer: '.$this->getCustomerEmail());
        $mailer->addEmailInfo($emailInfo);
        if ($copyTo && $copyMethod == 'bcc') {
            // Add bcc to customer email
            if ($storeOwner) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($storeOwner);
                foreach ($copyTo as $email) {
                    $emailInfo->addBcc($email);
                }
                $mailer->addEmailInfo($emailInfo);
            } else {
                foreach ($copyTo as $email) {
                    $emailInfo->addBcc($email);
                }
            }
        }

        // Email copies are sent as separated emails if their copy method is 'copy'
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
                'order'        => $this,
                'billing'      => $this->getBillingAddress(),
                'payment_html' => $paymentBlockHtml
            )
        );
        $mailer->send();

        $this->setEmailSent(true);
        $this->_getResource()->saveAttribute($this, 'email_sent');

        return $this;
    }
}
