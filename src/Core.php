<?php

namespace Octw\Aramex;


/**
*  The Core Class that contain the parameters array
*  Usage : -Define an instance (it should call the constructor) 
*          -Define your ClientInfo (should be given by Aramex)
*          -Call functions
*          -Enjoy Coding !! ;)
*/
class Core  
{
    protected $param;
    protected $soapClient;
    protected $accNum;
    protected $accEnt; 
    protected $accCntryCd;
    protected $accPin;
    protected $usrNm;
    protected $psrd;
    protected $ver;


    // Param Details -> Navigate to "https://www.aramex.com/docs/default-source/resourses/resourcesdata/shipping-services-api-manual.pdf"


    // WSDL Files:
    // test https://ws.dev.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc?singleWsdl
    // live https://ws.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc?singleWsdl

    /**
     * Basically The Contsructor 
     * @param : 1) None : will create the instance on test environment 
     *          2) ClientInfo : will create the instance on test or live environment (According to user ClientInfo credentials)
     */
    public function __construct()
    {
        $this->param = [
			'ClientInfo'  			=> [
				'AccountCountryCode'	=> '',
				'AccountEntity'		 	=> '',
				'AccountNumber'		 	=> '',
				'AccountPin'		 	=> '',
				'UserName'			 	=> '',
				'Password'			 	=> '',
				'Version'			 	=> ''
			],
			'Transaction' 			=> [
				'Reference1'			=> '', // extra param for response
				'Reference2'			=> '', // extra param for response
				'Reference3'			=> '', // extra param for response
				'Reference4'			=> '', // extra param for response
			],
			'LabelInfo'		=> config('aramex.LabelInfo'),
        ];


        $this->env = config('aramex.ENV');
        $this->accNum = config("aramex.".$this->env)['AccountNumber'];
        $this->accEnt = config("aramex.".$this->env)['AccountEntity'];
        $this->accCntryCd = config("aramex.".$this->env)['AccountCountryCode'];
        $this->accPin = config("aramex.".$this->env)['AccountPin'];
        $this->usrNm = config("aramex.".$this->env)['UserName'];
        $this->psrd = config("aramex.".$this->env)['Password'];
        $this->ver = config("aramex.".$this->env)['Version'];
        $this->param['ClientInfo'] = config("aramex.".$this->env);

        // $this->param['ClientInfo']['AccountCountryCode']= $this->accCntryCd;
		// $this->param['ClientInfo']['AccountEntity'] = $this->accEnt;
		// $this->param['ClientInfo']['AccountNumber'] = $this->accNum;
		// $this->param['ClientInfo']['AccountPin'] = $this->accPin;
		// $this->param['ClientInfo']['UserName'] = $this->usrNm;
		// $this->param['ClientInfo']['Password'] = $this->psrd;
		// $this->param['ClientInfo']['Version'] =$this->ver;
		
    }

    public function initializeShipment($shipper , $consignee , $details)
    {
        $this->param['Shipments'] = [
            'Shipment' => [
                'Shipper'	=> [
                    'AccountNumber' => $this->accNum,
                    'Contact'		=> [
                        'PersonName'			=> $shipper->PersonName,
                        'CompanyName'			=> config('aramex.company_name'),
                        'PhoneNumber1'			=> $shipper->PhoneNumber1,
                        'CellPhone'				=> $shipper->CellPhone,
                        'EmailAddress'			=> $shipper->EmailAddress,
                    ],
                    "PartyAddress"=> [
                        "Line1"             => $shipper->Line1,
                        "Line2"             => $shipper->Line2,
                        "Line3"             => $shipper->Line3,
                        "City"              => $shipper->City,
                        // "StateOrProvinceCode"=> null,
                        "PostCode"          => $shipper->ZipCode,
                        "CountryCode"       => $shipper->CountryCode,
                        "Longitude"         => 0,
                        "Latitude"          => 0
                    ],
                ],
                'Consignee'	=> [
                    'Reference1'	=> time(), // for response
                    'Reference2'	=> '', // for response
                    'AccountNumber' => $this->accNum, //Account Number 
                    'Contact'		=> [
                        'PersonName'			=> $consignee->PersonName,//Person Name 
                        'CompanyName'			=> $consignee->PersonName,
                        'PhoneNumber1'			=> $consignee->PhoneNumber1, //Phone Number
                        'CellPhone'				=> $consignee->CellPhone, //Cell Phone 
                        'EmailAddress'			=> $consignee->EmailAddress, // Email
                    ],
                    'PartyAddress'	=> [
                        'Line1'					=> $consignee->Line1,//Line1 
                        'Line2'					=> $consignee->Line2,//Line2 
                        'Line3'					=> $consignee->Line3,//Line3
                        'CountryCode'			=> $consignee->CountryCode, // Country Code
                        'City'                  => $consignee->City,
                    ],
                            
                ],
                
                'ShippingDateTime' => $details->ShippingDateTime, // Should be Filled
                "DueDate"=> $details->DueDate, // Should be Filled
                "Comments"=> $details->Comments, //Should Be Filled
                "PickupLocation"=> $details->PickupLocation, // Should be Filled
                "Attachments"=> null,
                "ForeignHAWB"=> null,
                "TransportType"=> "0",
                "PickupGUID"=> $details->PickupGUID,
                "Number"=> 0,
                'Details' => [						
                    'ActualWeight' => [
                        'Value'					=> $details->ActualWeight,
                        'Unit'					=> 'Kg'
                    ],
                    
                    'ProductGroup' 			=> $details->ProductGroup,
                    'ProductType'			=> $details->ProductType,
                    'PaymentType'			=> $details->PaymentType,
                    'PaymentOptions' 		=> $details->PaymentOptions, // it can be filled
                    'NumberOfPieces'		=> $details->NumberOfPieces,
                    'DescriptionOfGoods' 	=> $details->DescriptionOfGoods,
                    'GoodsOriginCountry' 	=> $details->GoodsOriginCountry,
                    
                    // Optionals Depending on Payment terms above
                    
                    'CollectAmount'			=> [
                        'Value'         => $details->CollectAmount,
                        'CurrencyCode'  => $details->CurrencyCode
                    ],

                    'CashOnDeliveryAmount'  => [
                        'Value'         => $details->CashOnDeliveryAmount,
                        'CurrencyCode'  => $details->CurrencyCode
                    ],
                    
                    'InsuranceAmount'       => [
                        'Value'         => $details->InsuranceAmount,
                        'CurrencyCode'  => $details->CurrencyCode
                    ],
                    
                    'CashAdditionalAmount'  => [
                        'Value'         => $details->CashAdditionalAmount,
                        'CurrencyCode'  => $details->CurrencyCode
                    ],
                    'CashAdditionalAmountDescription' => $details->CashAdditionalAmountDescription,
                    
                    'CustomsValueAmount'    => [
                        'Value'         => $details->CustomsValueAmount,
                        'CurrencyCode'  => $details->CurrencyCode
                    ],

                ]
            ]
        ];

    }

  //   public function fillShipperInfo($shipper) // Should be static (IDK)
  //   {  
  //       $this->param['Shipments']['Shipment']['Shipper']['Contact']['PersonName'] = $shipper->name; // 'SomeName',
		// $this->param['Shipments']['Shipment']['Shipper']['Contact']['PhoneNumber1'] = $shipper->phone_number;// '077777',
		// $this->param['Shipments']['Shipment']['Shipper']['Contact']['CellPhone'] = $shipper->cell_phone_number;// '055555',
		// $this->param['Shipments']['Shipment']['Shipper']['Contact']['EmailAddress'] = $shipper->email;// 'email@somedomain.com',
		// $this->param['Shipments']['Shipment']['Shipper']['PartyAddress']['City'] = $shipper->city;// 'Dubai',
		// $this->param['Shipments']['Shipment']['Shipper']['PartyAddress']['Line1'] = $shipper->address_line1;// 'Line1 Address',
		// $this->param['Shipments']['Shipment']['Shipper']['PartyAddress']['Line2'] = $shipper->address_line2;// 'Line2 Address',
		// $this->param['Shipments']['Shipment']['Shipper']['PartyAddress']['Line3'] = $shipper->address_line3;// 'Line3 Address',
		// $this->param['Shipments']['Shipment']['Shipper']['PartyAddress']['StateOrProvinceCode'] = $shipper->province_code;// 'ProvinceCode',
		// $this->param['Shipments']['Shipment']['Shipper']['PartyAddress']['PostCode'] = $shipper->post_code;// 'Posatal Code',
		// $this->param['Shipments']['Shipment']['Shipper']['PartyAddress']['CountryCode'] = $shipper->country_code;// 'AE',
  //   }

  //   public function fillConsigneeInfo($consignee)
  //   {
  //       $this->param['Shipments']['Shipment']['Consignee']['AccountNumber'] = $consignee->account_number;
		// $this->param['Shipments']['Shipment']['Consignee']['Contact']['PersonName'] = $consignee->name;
		// $this->param['Shipments']['Shipment']['Consignee']['Contact']['CompanyName'] = $consignee->name;
		// $this->param['Shipments']['Shipment']['Consignee']['Contact']['PhoneNumber1'] = $consignee->phone_number;
		// $this->param['Shipments']['Shipment']['Consignee']['Contact']['CellPhone'] = $consignee->phone;
		// $this->param['Shipments']['Shipment']['Consignee']['Contact']['EmailAddress'] = $consignee->email;
		// $this->param['Shipments']['Shipment']['Consignee']['PartyAddress']['Line1'] = $consignee->line1;
		// $this->param['Shipments']['Shipment']['Consignee']['PartyAddress']['Line2'] = $consignee->line2;
		// $this->param['Shipments']['Shipment']['Consignee']['PartyAddress']['Line3'] = $consignee->line3;
		// $this->param['Shipments']['Shipment']['Consignee']['PartyAddress']['City'] = $consignee->city;
		// $this->param['Shipments']['Shipment']['Consignee']['PartyAddress']['CountryCode'] = $consignee->country_code;
  //   }

  //   public function fillShipmentObject($weight, $time , $ref1 = '', $ref2 ='', $ref3 = '')
  //   {
		// $this->param['Shipments']['Shipment']['Details']['PaymentOptions'] = "ARCC";
		// $this->param['Shipments']['Shipment']['Details']['CollectAmount']['Value'] = "0";
		// $this->param['Shipments']['Shipment']['Details']['ActualWeight']= [
		// 	'Value' => isset($weight) ?$weight : 1,
		// 	'Unit' => 'Kg'
		// ];
		// $this->param['Shipments']['Shipment']['Details']['Items']['Weight']['Value'] = $weight;
		// $this->param['Transaction']['Reference1'] = $ref1;
		// $this->param['Transaction']['Reference2'] = $ref2;
		// $this->param['Transaction']['Reference3'] = $ref3;
		// $time = strtotime('today midnight +12 hours +1 weeks'); // Must be changed
		// $this->param['Shipments']['Shipment']['ShippingDateTime'] = $time + 96709; 
  //   }


    public function initializePickup($pickupDetails, $pickupAddress){

		$this->param['Pickup'] = [	
            'Reference1'=> $pickupDetails->Reference1,
            'Reference2'=> $pickupDetails->Reference1,
			'PickupLocation' =>$pickupDetails->PickupLocation,
			'Status' => $pickupDetails->Status, 
			'PickupDate' => $pickupDetails->PickupDate,
			'ReadyTime' => $pickupDetails->ReadyTime,
			'LastPickupTime' => $pickupDetails->LastPickupTime, // +26 hours
			'ClosingTime' => $pickupDetails->ClosingTime, //+28 hours
			'PickupContact' => [
				'PersonName'	=> $pickupAddress->PersonName, // should be static 'SomeName',
				'CompanyName'	=> config('aramex.CompanyName'), // config file
				'PhoneNumber1'	=> $pickupAddress->PhoneNumber1, // should be static '0777777',
				'CellPhone'		=> $pickupAddress->CellPhone, // should be static '0555555',
				'EmailAddress'	=> $pickupAddress->EmailAddress // should be static 'email@somedomain.com'
			],
			'PickupAddress' => [
				'Line1' => $pickupAddress->Line1, // should be static 'Line1 Address',
				'Line2' => $pickupAddress->Line2, // should be static 'Line2 Address',
				'Line3' => $pickupAddress->Line3, // should be static 'Line3 Address',
				'CountryCode' => $pickupAddress->CountryCode, // should be static 'AE',
				'City' => $pickupAddress->City, // should be static 'Dubai'
                'PostCode' => $pickupAddress->ZipCode
			],
			'PickupItems' => [
				'PickupItemDetail' => [
					'ProductGroup' => $pickupDetails->ProductGroup,
					'Payment' => $pickupDetails->Payment,
					'ProductType' => $pickupDetails->ProductType,
					'NumberOfPieces' => '1',
					'ShipmentWeight' => [
						'Value' => $pickupDetails->Weight,
						'Unit' => 'Kg'
					],
					'NumberOfShipments' => 1,
					'ShipmentVolume'=> [
						'Value'=> $pickupDetails->Volume,
						'Unit'=>'Cm3'
					]
				]
			]
		];
    }

    public function initializePickupCancelation($guid , $comment)
    {
        $this->param['PickupGUID'] = $guid;
        $this->param['Comments'] = $comment;
    }

    public function getParam()
    {
        return $this->param;
    }

    public function initializeCalculateRate($param , $weight , $count){
        $this->param['ClientInfo'] = [
            "UserName" => "info@lemarais101.com",
            "Password" => 'Mnb123@$',
            "Version" => "v1",
            "AccountNumber" => "165318",
            "AccountPin" => "165265",
            "AccountEntity" => "DXB",
            "AccountCountryCode" => "AE",
        ];

        $this->param['OriginAddress'] = [
                "Line1" => "Dubai Logistics City", // Warehouse
                "Line2" => "Aramex Logistics Center", // Warehouse
                "Line3" => "", // Warehouse
                'City' => 'Dubai', // Warehouse
                'CountryCode' => 'AE' // Warehouse
        ];

        $this->param['DestinationAddress'] = $param->DestinationAddress;


        // $this->param['DestinationAddress'] = [
        //         "Line1" => $param,
        //         "Line2" => "Unit # 1",
        //         "Line3" => "",    
        //         'City' => 'Amman', // Destination
        //         'CountryCode' => 'JO' // Destination
        // ];

        $this->param['ShipmentDetails'] = [
                'PaymentType' => 'P',
                'ProductGroup' => 'EXP',
                // 'ProductGroup' => 'DOM',
                'ProductType' => 'EPX',
                // 'ProductType' => 'CDS',
                'ActualWeight' => ['Value' => $weight, 'Unit' => 'KG'],
                'NumberOfPieces' => $count
        ];
        if ($param->DestinationAddress['CountryCode'] == 'AE'){
            $this->param['ShipmentDetails']['ProductGroup'] = 'DOM';
            $this->param['ShipmentDetails']['ProductType'] = 'CDS';
        }

        $this->param["PreferredCurrencyCode"] = strtoupper(session('currency'));
    }
}
