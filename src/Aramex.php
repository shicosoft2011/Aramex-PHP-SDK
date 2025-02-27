<?php

namespace Octw\Aramex;

use Octw\Aramex\Core;
use Octw\Aramex\Helpers\AramexHelper;
use SoapClient;

/**
* The Package Interface that will be used in App\Http\ 
*/
class Aramex
{

	/**
	*
	*  @param array of pickup parameters
	*  @return object described in https://
	*/
	public static function createPickup($param = [])
	{
		// Define an instance from the core class.
		$aramex = new Core;
		// Import SoapCLient object from Aramex's endpoint. 
		$soapClient = new SoapClient('https://ws.dev.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc?singleWsdl');

		// Preparation for initializing pickup request (Extract the data). 
		$pickupAddress = AramexHelper::extractPickupAddressContact($param);     // unchangeable
        $pickupDetails = AramexHelper::extractPickupDetails($param);            // changeable

        // initialize pickup request.
        $aramex->initializePickup($pickupDetails , $pickupAddress);

        // call the SoapClient API.
        $call = $soapClient->CreatePickup($aramex->getParam());
        
        $ret = new \stdClass;
        // check the response.
        if ($call->HasErrors){

            // prepare return object with errors described in call response.
            $ret->error = 1;
            // No one knows what is the structure of the response 
            if (is_array($call->Notifications)){
                $ret->errors = $call->Notifications['Notification'];
            }
            else {
                $ret->errors = $call->Notifications->Notification;
            }
        }
        else {
        	
        	// extract helpful data from call response.
            $pickupGUID = $call->ProcessedPickup->GUID;
            $pickupId = $call->ProcessedPickup->ID;
            //Extra Stuffs TODO.


            // Prepare return object.
            $ret->error = 0;
            $ret->pickupGUID = $pickupGUID;
            $ret->pickupID   = $pickupId;
            
        }
        // return the prepared object.
        return $ret;    
	}

    public static function cancelPickup($pickupGuid , $commnet)
    {
        // Define an instance from the core class.
        $aramex = new Core;
        // Import SoapCLient object from Aramex's endpoint. 
        $soapClient = new SoapClient('https://ws.dev.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc?singleWsdl');

        $aramex->initializePickupCancelation($pickupGuid , $commnet);

        $call = $soapClient->CancelPickup($aramex->getParam());
        
        $ret = new \stdClass;

        if ($call->HasErrors){
            $ret->error = 1;
            $ret->errors = $call->Notifications['Notification'];
        }
        else {
            $ret = $call;
        }
        return $ret;
    }


    /**
    *
    * @param array of shipment parameters 
    * @return object described in https://
    **/
    public static function createShipment($param =[])
    {
        // Define an instance from the core class.
        $aramex = new Core;
        // Import SoapCLient object from Aramex's endpoint. 
        $soapClient = new SoapClient('https://ws.dev.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc?singleWsdl');

        $shipperAddress = AramexHelper::extractShipperAddressContact($param);
        $consigneeAddress = AramexHelper::extractConsigneeAddressContact($param);

        $shipmentDetails = AramexHelper::extractShipmentDetails($param);

        $aramex->initializeShipment($shipperAddress, $consigneeAddress, $shipmentDetails);

        $call =  $soapClient->CreateShipments($aramex->getParam());
       
        $ret = new \stdClass;

        if ($call->HasErrors) {
            $ret->error = 1;
            $ret->errors = $call->Shipments->ProcessedShipment->Notifications->Notification;
        }
        else{
            $ret = $call;
        }

        return $ret;
    }


}