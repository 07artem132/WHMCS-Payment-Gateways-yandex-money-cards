<?php

/**
 * 
 * @author Artem Ivanko <a_ivanko@service-voice.com>
 */
yandexmoney_loadLang();

function yandexmoney_config() {
    global $_LANG;

    $configarray = array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Yandex money'
        ),
        'receiver' => array(
            'FriendlyName' => $_LANG['yandexmoney_number_yandex_purse'],
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => $_LANG['yandexmoney_wallet_for_transfer_of_money'],
        ),
        'Secret' => array(
            'FriendlyName' => $_LANG['yandexmoney_Secret'],
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => $_LANG['yandexmoney_Secret_des'],
        ),
        'URLscript' => array(
            'FriendlyName' => $_LANG['yandexmoney_URL_script'],
            'Description' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/modules/gateways/callback/yandex.php',
        ),
        'author' => array(
            'FriendlyName' => $_LANG['yandexmoney_Author'],
            'Description' => 'Artem Ivanko <a href="mailto:a_ivanko@service-voice.com">a_ivanko@service-voice.com</a>',
        ),
        'Poweredby' => array(
            'FriendlyName' => $_LANG['yandexmoney_Powered_by'],
            'Description' => 'Service-Voice',
        )
    );
    return $configarray;
}

function yandexmoney_link($params) {
    global $_LANG;

    $fields = array();
    $fields['receiver'] = $params['receiver'];
    $fields['quickpay-form'] = 'shop';
    $fields['formcomment'] = $params['companyname'] . ' - ' . $_LANG['invoicenumber'] . $params['invoiceid'];
    $fields['short-dest'] = $_LANG['invoicenumber'] . $params['invoiceid'];
    $fields['writable-targets'] = 'false';
    $fields['writable-sum'] = 'false';
    $fields['comment-needed'] = 'false';
    $fields['label'] = $params['invoiceid'];
    $fields['targets'] = $params['companyname'] . ' - ' . $_LANG['invoicenumber'] . $params['invoiceid'];
    $fields['sum'] = number_format($params['amount'], 2, '.', '');
    $fields['need-fio'] = 'false';
    $fields['need-email'] = 'false';
    $fields['need-phone'] = 'false';
    $fields['need-address'] = 'false';
    $fields['paymentType'] = 'PC';

    $code = '<form method="POST" target="_top" action="https://money.yandex.ru/quickpay/confirm.xml">' . PHP_EOL;

    foreach ($fields as $key => $value) {
        $code .= '<input type="hidden" name="' . $key . '" value="' . $value . '"/>' . PHP_EOL;
    }

    $code .= '<input type="submit" value="' . $_LANG['invoicespaynow'] . '" class="button" /></form>' . PHP_EOL;

    logModuleCall($module = 'yandexmoney', $action = __FUNCTION__, $requeststring = [ 'params' => $params], $responsedata = null, $processeddata = ['code' => $code, 'fields' => $fields], $replacevars = null);

    return $code;
}

function yandexmoney_loadLang($lang = null, $default = 'russian') {
    global $_LANG, $CONFIG;
    $Langpath = (dirname(__FILE__) . '/lang/yandexmoney');

    if (empty($lang)) {
        $Language = isset($_SESSION['Language']) ? $_SESSION['Language'] : $CONFIG['Language'];
    } else {
        $Language = $lang;
    }

    $LanguageFile = $Language . '.php';
    $LanguageFileDefault = $default . '.php';

    if (file_exists($Langpath . '/' . $LanguageFileDefault)) {
        include $Langpath . '/' . $LanguageFileDefault;
    }

    if (file_exists($Langpath . '/' . $LanguageFile)) {
        include $Langpath . '/' . $LanguageFile;
    }
}
