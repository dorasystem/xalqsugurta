# OSGOR va OSGOP Sug'urta Polis API

## Endpoint'lar

### OSGOP (ОСГОП - Обязательное Страхование Гражданской Ответственности Перевозчиков)

**Base URL:** `http://online.xalqsugurta.uz/xs/ins/eshop`

- **Калькулятор:** `POST /osgopcalc`
- **Шартнома киритиш:** `POST /osgop`

### OSGOR (ОСГОР - Обязательное Страхование Гражданской Ответственности Работодателей)

**Base URL:** `http://online.xalqsugurta.uz/xs/ins/eshop`

- **Калькулятор:** `POST /osgorcalc`
- **Шартнома киритиш:** `POST /osgor`

---

## OSGOP API

### 1. OSGOP Калькулятор

**Endpoint:** `POST /osgopcalc`

**Request Body:**
```json
{
  "policies": [
    {
      "insuranceTermId": 7,
      "objects": [
        {
          "vehicle": {
            "vehicleTypeId": 2,
            "numberOfSeats": 5
          }
        }
      ]
    }
  ]
}
```

**Parametrlar:**
- `insuranceTermId` (integer) - Sug'urta muddati ID
- `vehicleTypeId` (integer) - Transport turi ID (qarang: Vehicle Type ID'lar)
- `numberOfSeats` (integer) - O'rindiqlar soni

---

### 2. OSGOP Шартнома киритиш

**Endpoint:** `POST /osgop`

**Headers:**
```
Content-Type: application/json
Authorization: Basic {base64_encoded_credentials}
```

#### 2.1. Yuridik shaxslar uchun

**Request Body:**
```json
{
  "number": "11-06/4003/11-000094",
  "sum": "1144000000",
  "contractStartDate": "2026-02-06",
  "contractEndDate": "2027-02-05",
  "regionId": "11",
  "areaTypeId": "1",
  "agencyId": "546",
  "comission": "0",
  "insurant": {
    "organization": {
      "inn": "200941865",
      "name": "\"CHIRCHIQ AVTOBUS TAKSOMOTOR SAROYI\" MAS'ULIYATI CHEKLANGAN JAMIYAT",
      "representativeName": "MANSUROV FURKAT MUXAMETOVICH",
      "address": "X.Maqsudov",
      "oked": "49310",
      "position": "Директор",
      "phone": "998911626280",
      "regionId": "11",
      "ownershipFormId": "130"
    }
  },
  "policies": [
    {
      "startDate": "2026-02-06",
      "endDate": "2026-02-05",
      "insuranceSum": "1144000000",
      "insuranceRate": "0.01510",
      "insurancePremium": "172744",
      "insuranceTermId": 4,
      "healthLifeDamageSum": 40000000,
      "propertyDamageSum": 4000000,
      "objects": [
        {
          "vehicle": {
            "isForeign": false,
            "techPassport": {
              "number": "0125706",
              "seria": "AAF"
            },
            "govNumber": "10261HBA",
            "regionId": "11",
            "modelCustomName": "ISUZU SAZ NP 26",
            "vehicleTypeId": "1",
            "issueYear": "2016",
            "bodyNumber": "РАКАМСИЗ",
            "numberOfSeats": "26",
            "engineNumber": "510739",
            "license": {
              "seria": "AT",
              "number": "2007142",
              "beginDate": "2026-02-01",
              "endDate": "2027-01-31",
              "typeCode": "ЙЎЛОВЧИЛАРНИ ШАҲАРДА ШАҲАР АТРОФИДА ШАҲАРЛАРАРО МИКРОАВТОБУС ҲАМДА АВТОБУСЛАРДА ТАШИШ"
            },
            "ownerOrganization": {
              "inn": "200941865",
              "name": "\"CHIRCHIQ AVTOBUS TAKSOMOTOR SAROYI\" MAS'ULIYATI CHEKLANGAN JAMIYAT",
              "representativeName": "MANSUROV FURKAT MUXAMETOVICH",
              "address": "X.Maqsudov",
              "oked": "49310",
              "position": "Директор",
              "phone": "998911626280",
              "regionId": "11",
              "ownershipFormId": "130"
            }
          }
        }
      ]
    }
  ]
}
```

#### 2.2. Jismoniy shaxslar uchun

**Request Body:**
```json
{
  "number": "15",
  "sum": "176000000",
  "contractStartDate": "2026-02-06",
  "contractEndDate": "2027-02-05",
  "regionId": "10",
  "areaTypeId": "1",
  "agencyId": "546",
  "comission": "0",
  "insurant": {
    "person": {
      "passportData": {
        "pinfl": "32703740640057",
        "seria": "AA",
        "number": "8039873"
      },
      "fullName": {
        "firstname": "BAXIT",
        "lastname": "YEGAMOV",
        "middlename": "MAMETOVICH"
      },
      "regionId": "11",
      "driverLicenseSeria": "AF",
      "driverLicenseNumber": 639561,
      "gender": "m",
      "birthDate": "1974-03-27",
      "address": "ТОШОВУЛ ҚФЙ, МЕВАЗОР МФЙ, БОЙТЕПА КЎЧАСИ, uy:Р/С",
      "residentType": 1,
      "countryId": "210",
      "phone": "998977323536"
    }
  },
  "policies": [
    {
      "startDate": "2026-02-06",
      "endDate": "2027-02-05",
      "insuranceSum": "176000000",
      "insuranceRate": "0.20293",
      "insurancePremium": "90024",
      "insuranceTermId": 7,
      "healthLifeDamageSum": 40000000,
      "propertyDamageSum": 4000000,
      "objects": [
        {
          "vehicle": {
            "isForeign": false,
            "techPassport": {
              "number": "3844021",
              "seria": "AAG"
            },
            "govNumber": "01J675KC",
            "regionId": "10",
            "modelCustomName": "LACETTI",
            "vehicleTypeId": "2",
            "issueYear": "2023",
            "bodyNumber": "XWB5V31BVPA606185",
            "numberOfSeats": "4",
            "engineNumber": "B15D213233012MTDX0037",
            "license": {
              "seria": "AT",
              "number": "2434079",
              "beginDate": "2025-03-27",
              "endDate": "2026-03-26",
              "typeCode": "YO'LOVCHILARNI SHAXAR, SHAXAR ATROFI VA SHAXARLARARO YENGIL AVTOMOBILLARDA TASHISH"
            },
            "ownerPerson": {
              "passportData": {
                "pinfl": "32703740640057",
                "seria": "AA",
                "number": "8039873"
              },
              "fullName": {
                "firstname": "BAXIT",
                "lastname": "YEGAMOV",
                "middlename": "MAMETOVICH"
              },
              "regionId": "11",
              "driverLicenseSeria": "AF",
              "driverLicenseNumber": 639561,
              "gender": "m",
              "birthDate": "1974-03-27",
              "address": "ТОШОВУЛ ҚФЙ, МЕВАЗОР МФЙ, БОЙТЕПА КЎЧАСИ, uy:Р/С",
              "residentType": 1,
              "countryId": "210",
              "phone": "998977323536"
            }
          }
        }
      ]
    }
  ]
}
```

**OSGOP Request Parametrlari:**

**Asosiy maydonlar:**
- `number` (string) - Shartnoma raqami
- `sum` (string) - Sug'urta summasi
- `contractStartDate` (string, format: YYYY-MM-DD) - Shartnoma boshlanish sanasi
- `contractEndDate` (string, format: YYYY-MM-DD) - Shartnoma tugash sanasi
- `regionId` (string) - Viloyat ID
- `areaTypeId` (string) - Hudud turi ID
- `agencyId` (string) - Agentlik ID
- `comission` (string) - Komissiya

**Insurant (Sug'urtalanuvchi):**
- Yuridik shaxslar uchun: `insurant.organization`
  - `inn` (string) - INN
  - `name` (string) - Tashkilot nomi
  - `representativeName` (string) - Vakil ismi
  - `address` (string) - Manzil
  - `oked` (string) - OKED kodi
  - `position` (string) - Lavozim
  - `phone` (string) - Telefon
  - `regionId` (string) - Viloyat ID
  - `ownershipFormId` (string) - Mulkiyat shakli ID

- Jismoniy shaxslar uchun: `insurant.person`
  - `passportData.pinfl` (string) - PINFL
  - `passportData.seria` (string) - Pasport seriyasi
  - `passportData.number` (string) - Pasport raqami
  - `fullName.firstname` (string) - Ism
  - `fullName.lastname` (string) - Familiya
  - `fullName.middlename` (string) - Otasining ismi
  - `regionId` (string) - Viloyat ID
  - `driverLicenseSeria` (string) - Haydovchilik guvohnomasi seriyasi
  - `driverLicenseNumber` (integer) - Haydovchilik guvohnomasi raqami
  - `gender` (string) - Jins ("m" yoki "f")
  - `birthDate` (string, format: YYYY-MM-DD) - Tug'ilgan sana
  - `address` (string) - Manzil
  - `residentType` (integer) - Yashash turi
  - `countryId` (string) - Mamlakat ID (210 - O'zbekiston)
  - `phone` (string) - Telefon

**Policies (Polislar):**
- `startDate` (string, format: YYYY-MM-DD) - Polis boshlanish sanasi
- `endDate` (string, format: YYYY-MM-DD) - Polis tugash sanasi
- `insuranceSum` (string) - Sug'urta summasi
- `insuranceRate` (string) - Sug'urta stavkasi
- `insurancePremium` (string) - Sug'urta mukofoti
- `insuranceTermId` (integer) - Sug'urta muddati ID
- `healthLifeDamageSum` (integer) - Sog'liq va hayot zarari summasi
- `propertyDamageSum` (integer) - Mulk zarari summasi

**Vehicle (Transport vositasi):**
- `isForeign` (boolean) - Chet el transporti
- `techPassport.number` (string) - Texpasport raqami
- `techPassport.seria` (string) - Texpasport seriyasi
- `govNumber` (string) - Davlat raqami
- `regionId` (string) - Viloyat ID
- `modelCustomName` (string) - Model nomi
- `vehicleTypeId` (string) - Transport turi ID
- `issueYear` (string) - Ishlab chiqarilgan yil
- `bodyNumber` (string) - Kuzov raqami
- `numberOfSeats` (string) - O'rindiqlar soni
- `engineNumber` (string) - Dvigatel raqami
- `license.seria` (string) - Ruxsatnoma seriyasi
- `license.number` (string) - Ruxsatnoma raqami
- `license.beginDate` (string, format: YYYY-MM-DD) - Ruxsatnoma boshlanish sanasi
- `license.endDate` (string, format: YYYY-MM-DD) - Ruxsatnoma tugash sanasi
- `license.typeCode` (string) - Ruxsatnoma turi kodi

**Owner (Egası):**
- Yuridik shaxslar uchun: `ownerOrganization` (yuridik shaxs maydonlari bilan bir xil)
- Jismoniy shaxslar uchun: `ownerPerson` (jismoniy shaxs maydonlari bilan bir xil)

---

## OSGOR API

### 1. OSGOR Калькулятор

**Endpoint:** `POST /osgorcalc`

**Request Body:** (Hali ma'lumot yo'q, lekin OSGOP kalkulyatoriga o'xshash bo'lishi mumkin)

### 2. OSGOR Шартнома киритиш

**Endpoint:** `POST /osgor`

**Headers:**
```
Content-Type: application/json
Authorization: Basic {base64_encoded_credentials}
```

**Request Body (Yuridik shaxslar uchun):**
```json
{
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
}
```

**OSGOR Request Parametrlari:**

**Asosiy maydonlar:**
- `number` (string) - Shartnoma raqami
- `sum` (string) - Sug'urta summasi
- `contractStartDate` (string, format: YYYY-MM-DD) - Shartnoma boshlanish sanasi
- `contractEndDate` (string, format: YYYY-MM-DD) - Shartnoma tugash sanasi
- `regionId` (string) - Viloyat ID
- `areaTypeId` (string) - Hudud turi ID
- `agencyId` (string) - Agentlik ID
- `comission` (string) - Komissiya

**Insurant (Sug'urtalanuvchi):**
- `insurant.organization` - Yuridik shaxs (OSGOP bilan bir xil struktura)
  - `checkingAccount` (string) - Hisob raqami (OSGOR uchun qo'shimcha)

**Policies (Polislar):**
- `issueDate` (string, format: YYYY-MM-DD) - Polis chiqarilgan sana
- `startDate` (string, format: YYYY-MM-DD) - Polis boshlanish sanasi
- `endDate` (string, format: YYYY-MM-DD) - Polis tugash sanasi
- `insuranceSum` (string) - Sug'urta summasi
- `insuranceRate` (string) - Sug'urta stavkasi
- `insurancePremium` (string) - Sug'urta mukofoti
- `insuranceTermId` (integer) - Sug'urta muddati ID
- `funeralExpensesSum` (string) - Dafn xarajatlari summasi
- `fot` (number) - FOT (Fond oplaty truda)

**Eslatma:** OSGOR uchun transport vositasi maydonlari yo'q, chunki bu ish beruvchilarning majburiy sug'urtasi.

---

## Vehicle Type ID'lar (Transport turlari)

| ID | Nomi (Uzbek) |
|---|---|
| 1 | Автобуслар (Buses) |
| 2 | Енгил автомобиллар (Light vehicles / Passenger cars) |
| 3 | Тиркамалар (Trailers) |
| 4 | Мотоцикл ва мотороллерлар (Motorcycles and motor scooters) |
| 5 | Электромобиллар (Electric vehicles) |
| 6 | Юк автомобиллар (Cargo vehicles / Trucks) |
| 7 | Микроавтобуслар (Minibuses) |
| 8 | Спецтехника (Special equipment) |
| 9 | Бошқа ер усти транспорт воситалари (Other ground vehicles) |
| 10 | Юк-йўловчи транспорт воситалари (Cargo-passenger vehicles) |
| 11 | Ярим тиркамалар (Semi-trailers) |
| 12 | Троллейбуслар (Trolleybuses) |
| 13 | Трамвайлар (Trams) |
| 14 | Метрополитен (Metro / Subway) |
| 15 | Темир йўл транспорти (Railway transport) |
| 16 | Ҳаво транспорти (Air transport) |
| 17 | Бошқа транспорт воситалари (Other vehicles) |

---

## Farqlar: OSGOP vs OSGOR

| Xususiyat | OSGOP | OSGOR |
|---|---|---|
| **Maqsad** | Tashuvchilarning majburiy sug'urtasi | Ish beruvchilarning majburiy sug'urtasi |
| **Transport** | Transport vositasi maydonlari mavjud | Transport vositasi maydonlari yo'q |
| **Insurant** | Jismoniy va yuridik shaxslar | Faqat yuridik shaxslar |
| **Policies** | `healthLifeDamageSum`, `propertyDamageSum` | `funeralExpensesSum`, `fot` |
| **Vehicle Info** | To'liq transport ma'lumotlari talab qilinadi | Transport ma'lumotlari talab qilinmaydi |

---

## Qo'shimcha ma'lumotlar

- OSGOR калькулятор: https://t.me/c/2628864977/576
