<?php


class SendMessage
{
    const SLEEP = 1;
    const COOKIE_FILE   =   "cookie.txt";
    const AUTH_URL      =   "http://cinema-hd.ru/index/sub/";
    const MAIN_URL      =   "http://cinema-hd.ru/";
    const COMMENT_URL   =   "http://cinema-hd.ru/index/";
    const DEBUG_CURL    =   0;
    const FAILED_DOWNLOAD_SLEEP = 2;
    const ATTEMPTS = 5;


    private $text       =   "";

    public function __construct($data, $message)
    {
        echo "\nStarted process of login";

        $this->text = trim($message);
        $this->login( self::AUTH_URL, $data);

        echo "\nTrime text: " . $this->text;

        echo "\nFinished process of login";


    }

    private function login($url, $data)
    {
        $page = $this->loadMainPage();
        if ($page)
        {
            $html = new DOMDocument();
            $html->preserveWhiteSpace = false;
            if ($html->loadHTML($page))
            {
                $xPathExt = " //*[@id=\"log-in\"]/span";
                $xPath = new DOMXPath($html);
                $nodelist = $xPath->query($xPathExt);
            }
        }
        return $this->initCurl($url, $url, $data);
    }

    private function checkCookieFile()
    {
        echo "\nChecking cookie file";

        if (file_exists (self::COOKIE_FILE))
        {
            echo "\nCookie file exists";
        }
        else
        {
            die("\n\n\nCookie file doesn't exists. ERROR!! Letters have not sent\n\n\n");
        }

        return true;
    }

    private function isAuth( $data ){
        return preg_match('|Профиль|', $data);
    }

    public function send()
    {
        if ($this->checkCookieFile()) {
            $firstNewsPage = $this->getFirstNewsPage();
            $this->sendMessage($firstNewsPage, $this->text);
        }
    }


    private function loadMainPage()
    {
        sleep(self::SLEEP);
        $data = file_get_contents(self::MAIN_URL);
        if (!$data)
        {
            echo "Error load page " . MAIN_URL . ". New attempt.\r\n";
            for ($i = 0; $i < self::ATTEMPTS; $i++)
            {
                sleep(self::FAILED_DOWNLOAD_SLEEP);
                $data = file_get_contents(self::MAIN_URL);
                if ($data)
                {
                    return $data;
                }
            }
        }

        return $data;
    }


    public function getFirstNewsPage()
    {
        $page = $this->loadMainPage();
        if ($page)
        {
            $html = new DOMDocument();
            $html->preserveWhiteSpace = false;
            if ($html->loadHTML($page))
            {
                echo "\nLogin?: ";
                echo $this->isAuth($page)?'Success':'Failed';
                $xPathExt = "//*[@id=\"entryID22313\"]/article/div/h3/a";
                $xPath = new DOMXPath($html);
                $nodelist = $xPath->query($xPathExt);

                foreach($nodelist as $n)
                {
                    return $n->getAttribute('href');
                }
            }
        }

        return "";
    }

    private function sendMessage($url, $text)
    {
            echo "\nurl send = ".$url;

            $data = "sos=1809785376&message".$text."&subs=2&ssid=VbkBp_HG&a=36&m=7&id=22313&soc_type=&data=&_tp_=xml";
            echo "\nsend message: ".$text;
            return $this->initCurl(self::COMMENT_URL, self::MAIN_URL, $data);

    }

    private function initCurl($url, $referer, $sendString)
    {
        $ch = curl_init();

        if( strtolower((substr($url, 0, 5)) == 'https') )
        {
            // если соединяемся с https
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        curl_setopt($ch, CURLOPT_URL, $url);

        // откуда пришли на эту страницу
        if ($referer != "")
        {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }

        // cURL будет выводить подробные сообщения о всех производимых действиях
        curl_setopt($ch, CURLOPT_VERBOSE, self::DEBUG_CURL);

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        //curl_setopt($ch, CURLOPT_POSTFIELDS,"login=".$login."&passwd=".$pass . "&action=login")
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sendString);

        curl_setopt($ch, CURLOPT_USERAGENT, "User-Agent=Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.110 Safari/537.36");
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //сохранять полученные COOKIE в файл
        curl_setopt($ch, CURLOPT_COOKIEFILE, self::COOKIE_FILE);
        curl_setopt($ch, CURLOPT_COOKIEJAR, self::COOKIE_FILE);

        $result=curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}