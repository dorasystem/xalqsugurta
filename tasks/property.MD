  $info = [
            "number" => "269116",
            "sum" => 3319756800,
            "contractStartDate" => $insuranceStartDate,
            "contractEndDate" => $insuranceEndDate,
            "regionId" => !empty($form['regionId']) ? $form['regionId'] : 18,
            "areaTypeId" => 1,
            "agencyId" => 28,
            "comission" => 0,
            "insuranceProductName" => "МОЛ-МУЛКНИ СУҒУРТА ҚИЛИШ",
            "formOfInsuranceId" => 2,
            "insuranceTypeId" => 999,
            "contractLink" => "https://impexonline.uz/trules/18-molmulk.pdf",
            "exchangeRate" => 12827.9,
            "uprAccountingGroupId" => 1,
            "insurant" => [
                "person" => [
                    "passportData" => [
                        "pinfl" => $form['pinfl'] ?? '',
                        "seria" => $form['passportSeries'] ?? '',
                        "number" => $form['passportNumber'] ?? '',
                    ],
                    "fullName" => [
                        "firstname" => $form['firstName'] ?? '',
                        "lastname" => $form['lastName'] ?? '',
                        "middlename" => $form['middleName'] ?? '',
                    ],
                    "regionId" => !empty($form['regionId']) ? $form['regionId'] : 18,
                    "gender" => ($form['gender'] ?? "1") == "1" ? "m" : "f",
                    "birthDate" => $form['birthDate'] ?? '',
                    "address" => !empty($form['address']) ? $form['address'] : "Ko‘rsatilmagan",
                    "residentType" => 1,
                    "countryId" => 210,
                    "phone" => $phoneNumber ?? '998910739373',
                ]
            ],
            "policies" => [
                [
                    "paymentConditionsId" => 3,
                    "startDate" => $insuranceStartDate,
                    "endDate" => $insuranceEndDate,
                    "insuranceSum" => $selectedInsurance['coverage'],
                    "insuranceRate" => 0.2,
                    "insurancePremium" => $selectedInsurance['policyCost'],
                    "insuranceTermId" => 6,
                    "ruleLink" => "https://impexonline.uz/trules/18-molmulk.pdf",
                    "objects" => [
                        [
                            "classes" => [15, 16],
                            "risks" => "МОЛ-МУЛКНИ СУҒУРТА ҚИЛИШ",
                            "insuranceSum" => $selectedInsurance['coverage'],
                            "insuranceRate" => 0.2,
                            "insurancePremium" => $selectedInsurance['policyCost'],
                            "price" => $selectedInsurance['coverage'],
                            "others" => [
                                "ownerPerson" => [
                                    "fullName" => [
                                        "firstname" => $form['firstName'] ?? '',
                                        "lastname" => $form['lastName'] ?? '',
                                        "middlename" => $form['middleName'] ?? '',
                                    ],
                                    "passportData" => [
                                        "pinfl" => $form['pinfl'] ?? '',
                                        "seria" => $form['passportSeries'] ?? '',
                                        "number" => $form['passportNumber'] ?? '',
                                    ],
                                    "regionId" => !empty($form['regionId']) ? $form['regionId'] : 18,
                                    "gender" => ($form['gender'] ?? "1") == "1" ? "m" : "f",
                                    "birthDate" => $form['birthDate'] ?? '',
                                    "address" => !empty($form['address']) ? $form['address'] : "Ko‘rsatilmagan",

                                    "residentType" => 1,
                                    "countryId" => 210,
                                    "phone" => $phoneNumber ?? '998910739373',
                                ],
                                "isForeign" => false,
                                "otherObjectTypeId" => 1,
                                "name" => ($form['firstName'] ?? '') . " " . ($form['lastName'] ?? ''),
                                "measureTypeId" => 12,
                                "volume" => 1,
                                "address" => $insuranceCadasterAddress,
                                "regionId" => !empty($form['regionId']) ? $form['regionId'] : 18,
                            ]
                        ]
                    ]
                ]
            ]
        ];

🧾 Task Description (in English)

This property belongs to the Insurance module and represents the data template that needs to be sent to the InsuranceApiService.

Please add validation rules for the request that will be handled at the route:
@routes/insurance/accident.

In the main.blade.php form, make sure all input names reflect their corresponding object paths.
For example, if a field belongs to policies.payment, then the input name should be:

<input name="policies['payment']" ...>


This structure ensures that the request data maps correctly to the nested object, making both validation and request handling easier to understand.

Please write the code in a clear, human-readable way, following the same structure and conventions as the Order creation logic (including form, request validation, HTTP response, etc.).

The final implementation should include:

Blade form (with properly named inputs)

Form Request class (with validation rules)

Controller method handling the request

Structured HTTP response consistent with the existing OrderCreate flow

////

2. refactor property/main.blade.php code. 
