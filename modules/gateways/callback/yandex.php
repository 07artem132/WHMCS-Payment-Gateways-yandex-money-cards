<?php

/**
 * 
 * @author Artem Ivanko <a_ivanko@service-voice.com>
 */
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';


logModuleCall($module = 'yandex', $action = 'Start_Vertifi_Transaction', $requeststring = $_POST);

switch ($_POST['notification_type']) {
    case 'p2p-incoming':
        $gatewayModuleName = 'yandexmoney';
        break;
    case 'card-incoming':
        $gatewayModuleName = 'yandexcards';
        break;
    default:
        logModuleCall($module = 'yandex', $action = 'Case: notification_type', $requeststring = $_POST, $responsedata = 'Exception Unknown notification_type = ' . $_POST['notification_type']);
        throw new Exception('Unknown notification_type = ' . $_POST['notification_type']);
}

logModuleCall($module = 'yandex', $action = 'switch_notification_type', $requeststring = $_POST['notification_type'], $responsedata = $gatewayModuleName);

$gatewayParams = getGatewayVariables($gatewayModuleName);

logModuleCall($module = 'yandex', $action = 'get_Gateway_Variables', $requeststring = ['gatewayModuleName' => $gatewayModuleName], $responsedata = ['gatewayParams' => $gatewayParams]);

if ($_POST['operation_id'] != 'test-notification') {
    $invoiceId = checkCbInvoiceID($_POST['label'], $gatewayParams['name']);

    checkCbTransID($_POST['operation_id']);
}

$string = $_POST['notification_type'] . '&' . $_POST['operation_id'] . '&' . $_POST['amount'] . '&' . $_POST['currency'] . '&' . $_POST['datetime'] . '&' . $_POST['sender'] . '&' . $_POST['codepro'] . '&' . $gatewayParams['Secret'] . '&' . $_POST['label'];

$hashString = hash("sha1", $string);

logModuleCall($module = 'yandex', $action = 'create hash', $requeststring = ['string' => $string], $responsedata = ['hashString' => $hashString]);

if ($hashString != $_POST['sha1_hash']) {
    logModuleCall($module = 'yandex', $action = 'verification hash', $requeststring = ['my_hash_String' => $hashString, 'sha1_hash' => $_POST['sha1_hash']], $responsedata = ['status' => 'error']);
    logTransaction($gatewayParams['name'], $_POST + ['my_hash_String' => $hashString], 'verification hash ERROR');
    throw new Exception('verification hash ERROR');
}
logModuleCall($module = 'yandex', $action = 'verification hash', $requeststring = ['my_hash_String' => $hashString, 'sha1_hash' => $_POST['sha1_hash']], $responsedata = ['status' => 'ok']);

if ($_POST['operation_id'] == 'test-notification') {
    logTransaction($gatewayParams['name'], $_POST + ['my_hash_String' => $hashString], 'test OK');
    die();
}

logTransaction($gatewayParams['name'], $_POST + ['my_hash_String' => $hashString], 'success');

addInvoicePayment($invoiceId, $_POST['operation_id'], $_POST['amount'], $_POST['withdraw_amount'] - $_POST['amount'], $gatewayModuleName);
