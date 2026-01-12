AAG   0756068  01L097RB

AD   0677482  32607873920010


http://127.0.0.1:8080/uz/payment/12


<!-- Gazbalon -->

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




<!-- Gazbalon -->




{
  "vehicle": {
    "govNumber": "90G388AB",
    "engineNumber": "B15D212250312MTAX0612",
    "issueYear": 2025,
    "modelCustomName": "COBALT",
    "techPassport": {
      "issueDate": "2025-11-14",
      "number": "7533427",
      "seria": "AAG"
    },
    "regionId": 22,
    "bodyNumber": "XWBJA69VESA538932",
    "terrainId": 2,
    "typeId": 1
  },
  "owner": {
    "organization": {
      "inn": null
    },
    "person": {
      "passportData": {
        "pinfl": "32307863070030",
        "issuedBy": "БАГАТСКИЙ РОВД ХОРЕЗМСКОЙ ОБЛАСТИ",
        "number": "1122720",
        "issueDate": "2022-02-23",
        "seria": "AD"
      },
      "birthDate": "1986-07-23",
      "fullName": {
        "firstname": "ISROIL",
        "middlename": "ISMOILOVICH",
        "lastname": "QUTLIMUROTOV"
      }
    },
    "applicantIsOwner": "true"
  },
  "applicant": {
    "person": {
      "passportData": {
        "number": "1122720",
        "seria": "AD",
        "issuedBy": "БАГАТСКИЙ РОВД ХОРЕЗМСКОЙ ОБЛАСТИ",
        "issueDate": "2022-02-23",
        "pinfl": "32307863070030"
      },
      "phoneNumber": "998919122413",
      "birthDate": "1986-07-23",
      "fullName": {
        "firstname": "ISROIL",
        "lastname": "QUTLIMUROTOV",
        "middlename": "ISMOILOVICH"
      },
      "gender": "m",
      "districtId": 2207,
      "regionId": 22
    },
    "organization": {
      "phoneNumber": "",
      "inn": "",
      "name": ""
    },
    "citizenshipId": 1,
    "address": "Ортиқ Отажонов МФЙ, Мафтункор кучаси, 219-уй",
    "email": "",
    "residentOfUzb": 1
  },
  "details": {
    "specialNote": "",
    "insuredActivityType": "OSAGO",
    "issueDate": "2025-11-14",
    "startDate": "2025-11-14",
    "endDate": "2026-11-14",
    "driverNumberRestriction": false
  },
  "drivers": [],
  "cost": {
    "discountId": 1,
    "sumInsured": "40000000",
    "contractTermConclusionId": 1,
    "commission": 10000,
    "insurancePremium": 120000,
    "discountSum": 0,
    "useTerritoryId": 2,
    "insurancePremiumPaidToInsurer": 120000
  }
}
