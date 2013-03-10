<?php
namespace Local;
use \Zend\Registry as Zend_Registry;
///use \Zend\Http\Client;
if (!function_exists('curl_init')) {
    throw new Exception('Launchrock needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
    throw new Exception('Launchrock needs the JSON PHP extension.');
}

class Launchrock {


    var $siteId = '';       # Your API key, get it from Launchrock.com
    var $urlPrefix = '';    # Your referral url, minus the actual referral id, e.g. 'example.com/?refid='
    var $url = '';     # The domain of your site as you registered it at Launchrock, e.g. 'example.com'
    var $lrVersion = "v1";  # API version

    public function __construct()
    {
//        $config = \Zend_Registry::get('config')->launchrock;
        $this->siteId = "7PSL1PRL"; //$config->get('launchrockSiteId');
    }

    public function submit($method, $data) {
        $values = array(
            'url'   => $this->url,
            'site_id'     => $this->siteId,
        );
        $values = array_merge($values, $data);
        $postString = $this->buildPostString($values);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://platform.launchrock.com/'.$this->lrVersion."/".$method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, count($values));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        $data = curl_exec($ch);

        # Launchrock returns json surrounded by parentheses and some whitespace,
        # so we have to trim those out to keep valid json
        $data = trim(trim($data), '()');

        return json_decode($data);
    }

    private function buildPostString($values) {
        $postString = '';
        foreach($values as $key => $value) {
            $postString .= $key . '=' . urlencode($value) . '&';
        }
        return trim($postString, '&');
    }


    public function createUser($user) {
        $response = $this->submit("createSiteUser",
            array(
                "email"=>$user->getEmail(),
                "password"=>"",
                "parent_id"=>"",
                "ip"=>$_SERVER['REMOTE_ADDR'],
            )
        );
        if ( $response[0]->response->status == "OK" ) {
            return $response[0]->response->site_user->UID;

        } else {
            throw new \Exception($response[0]->response->message);
        }
    }

    public function createChannelLink($user, $channel) {
        $createLinksRequest = $this->submit("createSiteUserChannelLink",
            array(
                "user_id"=>$user->getLrUid(),
                "channel"=>$channel,
                "url"=>"http://signup.shopnbrag.com",
                "shortened_id"=>""
            )
        );
        if ($createLinksRequest[0]->response->status == "OK") {
            return $createLinksRequest[0]->response->channel_link;
        } else {
            throw new \Exception($createLinksRequest[0]->response->message);
        }
    }

}

?>

