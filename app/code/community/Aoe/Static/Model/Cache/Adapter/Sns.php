<?php

/**
 * Class Aoe_Static_Model_Cache_Adapter_Sns
 */
class Aoe_Static_Model_Cache_Adapter_Sns extends Aoe_Static_Model_Cache_Adapter_Varnish
{

    /**
     * @var string
     */
    protected $snsTopic;

    /**
     * @var \Aws\Sns\SnsClient
     */
    protected $snsClient;


    public function getSnsTopic()
    {
        if (is_null($this->snsTopic)) {
            $this->snsTopic = Mage::getStoreConfig('dev/aoestatic/snsTopic');
            if (empty($this->snsTopic)) {
                throw new Exception('Invalid SNS topic');
            }
        }
        return $this->snsTopic;
    }

    public function getSnsClient()
    {
        if (is_null($this->snsClient)) {
            if (!class_exists('\Aws\Sns\SnsClient')){
                throw new Exception('Missing aws/aws-sdk-php');
            }

            // use EC2 instance profile of ENV vars to configure the client
            $this->snsClient = new \Aws\Sns\SnsClient([
                'version' => '2010-03-31',
                'region' => Mage::getStoreConfig('dev/aoestatic/snsRegion')
            ]);
        }
        return $this->snsClient;
    }

    protected function sendRequests(array $actions)
    {
        Mage::log('[Aoe_Static SNS] Publish SNS message');
        $this->getSnsClient()->publish([
            'Message' => json_encode($actions),
            'Subject' => 'Aoe_Static',
            'TopicArn' => $this->getSnsTopic(),
        ]);
        return array();
    }

}
