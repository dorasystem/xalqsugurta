

http://127.0.0.1:8080/uz/payment/2 275 000

<!-- Gazbalon uchun api product start -->

Controller : @app/Http/Controllers/Insurence/GasBallonController.php

route : @routes/insurence/gas.php

blades: @resources/views/pages/insurence/gas/

Basic Auth:
gazballonsayt
dorasystem1

http://online.xalqsugurta.uz/xs/ins/unv/gazballonsayt/InitiateTransactionRequest
http://online.xalqsugurta.uz/xs/ins/unv/gazballonsayt/PerformTransactionRequest
http://online.xalqsugurta.uz/xs/ins/unv/gazballonsayt/PolicyIssuanceRequest

{
"customer": {
"address": "г. Ташкент, ул. Мира, д. 1",
"birth_date": "01.11.1999",
"bxm": 1234,
"full_name": "Петров Петр Петрович",
"gender": 1,
"inn": 322114566,
"oked": 12345,
"passport": "AB1234567",
"phone": "998901234567",
"pinfl": "12345678901234",
"ras_sum": "12345678901234567890",
"representativename": "Иванов Иван Иванович",
"subj_mfo": 11111
},
"loan_info": {
"cadastr_info": {
"address": "Адрес имущества",
"building_type": 1,
"cadastr_issue_date": "01.01.2026",
"cadastr_number": "01:02:03:04:05",
"country": 210,
"description": "г. Ташкент, ул. Мира, д. 5",
"districtid": 1009,
"is_foreign": 0,
"is_owner": 1,
"name": "Название имущество",
"note": "Информация об имуществе",
"region_code": "10",
"regionid": 10,
"right_land_type": 1,
"subject_full_name": "Иванов Иван Иванович",
"sum_bank": 95000000
},
"claim_id": "987654321",
"contract_date": "01.01.2026",
"contract_number": "123-D",
"e_date": "31.12.2026",
"loan_type": "35",
"s_date": "01.01.2026"
},
"subject": "P"
}

Bu API bilan: 1. Gaz ballon Sug'urtasi - loan_type 35 2. Mening uyim- loan_type 36 3. Kasko - loan_type 37
Yuboriladi !!!



<!-- Gazbalon  end -->

<!-- Accident uchun api product start -->

Controller : @app/Http/Controllers/Insurence/AccidentController.php

route : @routes/insurence/accident.php

blades : @resources/views/pages/insurence/accident/

{
  "applicant": {
    "person": {
      "passportData": {
        "pinfl": "30504790580179",
        "seria": "AA",
        "number": "6919045",
        "issuedBy": "УВД ТАШКЕНТСКОЙ ОБЛАСТИ",
        "issueDate": "2014-09-04"
      },
      "fullName": {
        "firstname": "ILHOM",
        "lastname": "KARIMOV",
        "middlename": "ISMOILOVICH"
      },
      "residentType": 1,
      "phone": "998933994488",
      "birthDate": "1979-04-05",
      "address": "АЛПОМИШ МФЙ, БУЮК ИПАК ЙУЛИ КЎЧАСИ, uy:162В/31",
      "countryId": 210,
      "regionId": 10,
      "districtId": 1009
    }
  },
  "details": {
    "startDate": "2025-06-28",
    "endDate": "2026-06-27",
    "productCode": "personal_accident"
  },
  "cost": {
    "insurancePremium": 300000,
    "sumInsured": 60000000
  },
  "persons": [
    {
      "residentType": 1,
      "passportData": {
        "pinfl": "30504790580179",
        "seria": "AA",
        "number": "6919045",
        "issuedBy": "УВД ТАШКЕНТСКОЙ ОБЛАСТИ",
        "issueDate": "2014-09-04"
      },
      "fullName": {
        "firstname": "ILHOM",
        "lastname": "KARIMOV",
        "middlename": "ISMOILOVICH"
      },
      "phone": "998933994488",
      "birthDate": "1979-04-05",
      "address": "АЛПОМИШ МФЙ, БУЮК ИПАК ЙУЛИ КЎЧАСИ, uy:162В/31",
      "countryId": 210,
      "regionId": 10,
      "districtId": 1009,
      "insurancePremium": 250000,
      "sumInsured": 50000000
    },
    {
      "residentType": 1,
      "passportData": {
        "pinfl": "32302840200028",
        "seria": "AB",
        "number": "2148705",
        "issuedBy": "Mirabad IIV",
        "issueDate": "2020-09-04"
      },
      "fullName": {
        "firstname": "JAMSHID",
        "lastname": "KARIMOV",
        "middlename": "ISRAILOVICH"
      },
      "phone": "998901870285",
      "birthDate": "1979-04-05",
      "address": "Nukus 86-6",
      "countryId": 210,
      "regionId": 10,
      "districtId": 1009,
      "insurancePremium": 50000,
      "sumInsured": 10000000
    }
  ]
}




{
  "details": {

    "productCode": "athletes_accident"
  },
  "persons": [
    {
      "sumInsured": "50000000"
    },
    {
      "sumInsured": "10000000"
    }
  ]
}

<!-- Accident  end -->


<!-- Osgor uchun api product start -->

Controller : @app/Http/Controllers/Insurence/OsgorController.php  osgo

route : @routes/insurence/osgor.php

blades : @resources/views/pages/insurence/osgor/

http://online.xalqsugurta.uz/xs/ins/eshop/osgorcalc - ОСГОР калькулятор
http://online.xalqsugurta.uz/xs/ins/eshop/osgor - ОСГОР шартнома киритиш

curl --location 'http://online.xalqsugurta.uz/xs/ins/eshop/osgor' \
--header 'Content-Type: application/json' \
--header 'Authorization: Basic ******' \
--data '{
  "number": "1-5/4-4/0025",
  "sum": "443261886.34",
  "contractStartDate": "2026-02-01",
  "contractEndDate": "2027-02-01",
  "regionId": "10",
  "areaTypeId": "1",
  "agencyId": "546",
  "comission": "0",
  "insurant": {
    "organization": {
      "inn": "207131619",
      "name": "\"GERMES-STOM\" MAS`ULIYATI CHEKLANGAN JAMIYAT",
      "representativeName": "IMAMOVA XADICHA SHARIFOVNA",
      "address": "BUNYOKOR SHOH KO CHASI, E-10 TEST",
      "oked": "86230",
      "position": "Директор",
      "checkingAccount": "20208000004936724001",
      "phone": "998977098855",
      "regionId": "10",
      "ownershipFormId": "130"
    }
  },
  "policies": [
    {
      "issueDate": "2026-02-01",
      "startDate": "2026-02-01",
      "endDate": "2027-02-01",
      "insuranceSum": "443261886.34",
      "insuranceRate": "0.05710",
      "insurancePremium": "253102.54",
      "insuranceTermId": 4,
      "funeralExpensesSum": "1020000",
      "fot": 443261886.34
    }
  ]
}'


ОСГОР калькулятор

curl --location 'http://online.xalqsugurta.uz/xs/ins/eshop/osgorcalc' \
--header 'Content-Type: application/json' \
--header 'Authorization: ••••••' \
--data '{
  "insurant": {
    "organization": {
      "oked": "58290"
    }
  },
  "policies": [
    {
      "fot": 500000000
    }
  ]
}'

<!-- Osgor  end -->


<!-- Gazbalon site   start -->

Bu API bilan: 1. Gaz ballon Sug'urtasi - loan_type  35
                          2. Mening uyim- loan_type  36
                          3. Kasko - loan_type 37
Yuboriladi !!!


Basic Auth:
gazballonsayt
dorasystem1



http://online.xalqsugurta.uz/xs/ins/unv/gazballonsayt/InitiateTransactionRequest
http://online.xalqsugurta.uz/xs/ins/unv/gazballonsayt/PerformTransactionRequest
http://online.xalqsugurta.uz/xs/ins/unv/gazballonsayt/PolicyIssuanceRequest


Bu API bilan: 1. Gaz ballon Sug'urtasi - loan_type  35
                          2. Mening uyim- loan_type  36
                          3. Kasko - loan_type 37
Yuboriladi !!!




{
  "customer": {
    "address": "г. Ташкент, ул. Мира, д. 1",
    "birth_date": "01.11.1999",
    "bxm": 1234,
    "full_name": "Петров Петр Петрович",
    "gender": 1,
    "inn": 322114566,
    "oked": 12345,
    "passport": "AB1234567",
    "phone": "998901234567",
    "pinfl": "12345678901234",
    "ras_sum": "12345678901234567890",
    "representativename": "Иванов Иван Иванович",
    "subj_mfo": 11111
  },
  "loan_info": {
    "claim_id": "987654321",
    "contract_date": "01.01.2026",
    "contract_number": "123-D",
    "e_date": "31.12.2026",
    "loan_amount": 50000000,
    "loan_type": "37",
    "object_brand": "CHEVROLET",
    "object_name": "NEXIA - 150",
    "s_date": "01.01.2026"
  },
  "organization": {
    "address": "г. Ташкент, ул. Мира, д. 2",
    "birth_date": "02.12.2012",
    "bxm": 54321,
    "full_name": "Иванов Иван Иванович",
    "gender": 0,
    "inn": 455677899,
    "mfo": 22222,
    "oked": 65432,
    "passport": "AD1234567",
    "phone": "998987654321",
    "pinfl": "43210987654321",
    "ras_sum": "09876543210987654321",
    "representativename": "Петров Петр Петрович",
    "subject": "P"
  },
  "subject": "P",
  "vehicle_info": {
    "bodynumber": "XWB5M31BDA277889",
    "enginenumber": "F18D32229751",
    "regnumber": "40Z202EA",
    "techpassport": {
      "number": "3332961",
      "seria": "AAC"
    },
    "type": 2,
    "year": 2011
  }
}

method 2

{
  "contract_date": "01.09.2026",
  "contract_id": 123456,
  "contract_number": "123-D",
  "e_date": "31.08.2027",
  "payment_date": "01.09.2026",
  "s_date": "01.09.2026"
}

Gaz ballon, mening uyim va kasko sug'urtasi uchun API bitta bo'ladi. Sizga qaysi sug'urta mahsuloti kerak bo'lsa loan_type qo'yib yuborsangiz bo'ldi!
<!-- Gazbalon site   end -->
