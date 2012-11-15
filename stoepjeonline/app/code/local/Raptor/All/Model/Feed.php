<?php
class Raptor_All_Model_Feed extends Mage_AdminNotification_Model_Feed {
    const XML_FEED_URL_PATH     = 'rall/feed/url';
    const XML_FREQUENCY_PATH    = 'rall/feed/frequency';
    const XML_LAST_UPDATE_PATH  = 'rall/feed/last_update';	
	
    /**
     * Check feed for modification
     *
     * @return Mage_AdminNotification_Model_Feed
     */
    public function checkUpdate()
    {
    	if (($this->getFrequency() + $this->getLastUpdate()) > time()) {
            return $this;
        }

        $feedData = array();

        $feedXml = $this->getFeedData();

        if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
            foreach ($feedXml->channel->item as $item) {
                $feedData[] = array(
                    'severity'      => (int)$item->severity,
                    'date_added'    => $this->getDate((string)$item->pubDate),
                    'title'         => (string)$item->title,
                    'description'   => (string)$item->description,
                    'url'           => (string)$item->link,
                );
            }

            if ($feedData) {
                Mage::getModel('adminnotification/inbox')->parse(array_reverse($feedData));
            }

        }
        $this->setLastUpdate();

        return $this;
    }	
    
    public function getFeedUrl() {
		$url = Mage::getStoreConfigFlag(parent::XML_USE_HTTPS_PATH) ? 'https://' : 'http://' . Mage::getStoreConfig(self::XML_FEED_URL_PATH);
		return $url;
	}
	
    public function getFrequency(){
        return Mage::getStoreConfig(self::XML_FREQUENCY_PATH) * 3600;
    }

    public function getLastUpdate(){
        return Mage::app()->loadCache('rall_notifications_lastcheck');
    }   

    public function setLastUpdate()
    {
        Mage::app()->saveCache(time(), 'rall_notifications_lastcheck');
        return $this;
    }    
	
	public function observe() {
		if (Mage::getSingleton('admin/session')->isLoggedIn()) {
			$feedModel  = Mage::getModel('rall/feed');
			$feedModel->checkUpdate();
		}
	}
}
?>