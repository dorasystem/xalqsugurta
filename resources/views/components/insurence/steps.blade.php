@props(['activeStep' => 1])

<section class="banner1" style="background-image: url('{{ asset('assets/img/step-bg.png') }}');">
    <h1 class="Gilroy-header">{{ __('insurance.step.title') }}</h1>

    <div class="container-vkladki order-3 order-xxl-2">
        <div id="vkladka1" class="{{ $activeStep >= 1 ? 'circle-active' : '' }}">
            <div class="circle">
                <h1>1</h1>
            </div>
            <div class="description">
                <p>{{ __('insurance.step.step1') }}</p>
            </div>
        </div>
        <div class="line">
            <div class="line1 {{ $activeStep >= 1 ? 'line-active' : '' }}">

            </div>
            <div class="line2 {{ $activeStep >= 2 ? 'line-active' : '' }}">

            </div>
        </div>
        <div id="vkladka2" class="{{ $activeStep >= 2 ? 'circle-active' : '' }}">
            <div class="circle">
                <h1>2</h1>
            </div>
            <p class="description">{{ __('insurance.step.step2') }}</p>
        </div>
        <div class="line">
            <div class="line3 {{ $activeStep >= 2 ? 'line-active' : '' }}">

            </div>
            <div class="line4">

            </div>
        </div>
        <div id="vkladka3" class="{{ $activeStep >= 3 ? 'circle-active' : '' }}">
            <div class="circle ">
                <h1>3</h1>
            </div>
            <p class="description">{{ __('insurance.step.step3') }}</p>
        </div>
    </div>
</section>

<style>
    * {
        box-sizing: border-box;
    }

    body {
        position: relative;
        font-family: "Arial", sans-serif;
    }

    .text-dark-blue {
        color: rgba(57, 49, 133, 1);
    }


    .btn-dark-blue {
        background-color: rgba(57, 49, 133, 1);
        border: 1px solid rgba(57, 49, 133, 1);
        color: white;
    }

    .btn-white {
        background-color: white;
        border: 1px solid rgba(57, 49, 133, 1);
        color: rgba(57, 49, 133, 1);
    }

    .btn-white:hover {
        background-color: rgba(57, 49, 133, 1);
        color: white;
        transition: 0.3s;
    }

    .btn-dark-blue:hover {
        background-color: white;
        color: rgba(57, 49, 133, 1);
        transition: 0.3s;
    }

    .border-dark-blue:hover {
        border: 1px solid rgba(57, 49, 133, 1);
    }

    .btn-dark-blue:hover .fa-file,
    .btn-dark-blue:hover .evro {
        color: rgba(57, 49, 133, 1);
        transition: 0.3s;
    }


    #menu {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        display: none;
        flex-wrap: wrap;
        z-index: 5;
        animation-name: menu-burger;
        animation-duration: 1s;
    }

    .menu-left {
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        z-index: 5;
        background-color: rgba(58, 50, 134, 1);
        width: 55%;
        overflow: auto;
        animation-name: left-menu;
        animation-duration: 1s;
    }

    @keyframes left-menu {
        from {
            transform: translateX(-200%);
        }

        to {
            transform: translateX(0%);
        }
    }

    .menu-right {
        position: fixed;
        top: 0;
        bottom: 0;
        right: -1%;
        z-index: 5;
        background-color: white;
        width: 45%;
        overflow: auto;
        animation-name: right-menu;
        animation-duration: 1s;
    }

    @keyframes right-menu {
        from {
            transform: translateX(200%);
        }

        to {
            transform: translateX(0%);
        }
    }

    .fa-close {
        color: white;
        font-size: 40px;
        float: left;
        clear: both;
        margin-left: 20px;
        margin-top: 20px;
        cursor: pointer;
    }

    .fa-close:hover {
        background-color: white;
        color: rgba(57, 49, 133, 1);
    }

    .text-light-grey {
        color: rgb(184, 184, 184);
        font-size: 20px;
    }

    .text-light-grey:hover {
        color: white;
        transition: 0.3s;
    }



    .text-gilroy {
        color: #5F55BB;
        font-family: 'Gilroy', sans-serif;
        font-weight: 400;
        font-style: Regular;
        font-size: 14px;
        line-height: 100%;
        letter-spacing: 0%;
        padding-right: 15px;
        padding-left: 15px;
    }




    .Gilroy-header {
        font-family: 'Gilroy', sans-serif;
        font-weight: 600;
        font-style: Semibold;
        font-size: Clamp(22px, calc(2.13vw + 13.99px), 55px);
        line-height: 100%;
        letter-spacing: 0%;
        color: rgba(57, 49, 133, 1);
        text-align: center;
        padding-top: clamp(50px, calc(4.53vw + 33px), 120px);
        padding-bottom: clamp(50px, calc(7.5vw + 21.84px), 166px);
    }



    .banner1 {
        background-repeat: no-repeat, no-repeat;
        background-size: contain;
        background-position: 350px 70px, right top;
        padding-bottom: clamp(60px, calc(9.06vw + 26px), 200px);
    }



    .icon-sot-sety {
        width: 25px;
        height: 25px;
    }

    .circle {
        width: clamp(60px, calc(2.58vw + 50.29px), 100px);
        height: clamp(60px, calc(2.58vw + 50.29px), 100px);
        background-color: white;
        border-radius: 50%;
        color: rgba(57, 49, 133, 1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Gilroy', sans-serif;
        font-weight: 600;
        font-size:
            /*clamp(16px, calc(1.07vw + 12.14px), 25px);*/
            16px;
        font-style: Semibold;
        line-height: 36px;
        letter-spacing: 0%;
        cursor: pointer;
        box-shadow: 0px 0px 10px 0px rgba(174, 167, 236, 0.25);

    }

    .circle-active .circle {
        background-color: rgba(57, 49, 133, 1);
        color: white;
    }

    .description {
        font-family: 'Gilroy', sans-serif;
        font-weight: 600;
        font-style: Semibold;
        font-size: clamp(14px, calc(0.38vw + 12.54px), 20px);
        line-height: 100%;
        letter-spacing: 0%;
        color: rgba(57, 49, 133, 1);
        margin-top: clamp(20px, calc(1.94vw + 12.71px), 50px);
        max-width: 233px;
    }

    .container-vkladki {
        width: 60%;
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-left: auto;
        margin-right: auto;
    }

    #vkladka1,
    #vkladka2,
    #vkladka3 {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .line {
        display: flex;
        width: 100%;
        height: 1px;
    }

    .line1,
    .line2,
    .line3,
    .line4 {
        width: 50%;
        border: 1px solid lightgrey;
    }

    .line-active {
        border: 1px solid darkgrey
    }

    .sos,
    .headphones {
        width: 70px;
        height: 70px;
    }

    .header-cost {
        font-family: 'Gilroy', sans-serif;
        font-weight: 600;
        font-style: Semibold;
        font-size: clamp(20px, calc(1.03vw + 16.11px), 36px);
        line-height: 100%;
        letter-spacing: 0%;
        color: rgba(57, 49, 133, 1);
        padding-bottom: 50px;
    }

    .type-transport-header {
        font-family: 'Gilroy', sans-serif;
        font-weight: 600;
        font-style: Semibold;
        font-size: 16px;
        line-height: 100%;
        letter-spacing: 0%;
        color: rgba(174, 167, 236, 1);
    }




    .form-check-input:checked {
        background-color: rgba(57, 49, 133, 1);
        border-color: rgba(57, 49, 133, 1);
    }

    .form-check-input {
        box-shadow: 0px 0px 5px 0px rgba(57, 49, 133, 0.25);
    }

    .form-check-label {
        font-family: 'Gilroy', sans-serif;
        font-weight: 600;
        font-style: Semibold;
        font-size: 18px;
        line-height: 100%;
        letter-spacing: 0%;
        color: rgba(57, 49, 133, 1);
        margin-left: 10px;
    }

    #main-content-1,
    #main-content-2,
    #main-content-3 {
        background-color: rgba(243, 244, 255, 1);
        display: none;
    }

    #main-content-3 {
        background-color: rgba(243, 244, 255, 1);
        background-image: url("./img/Group73.png"), url("./img/Group126.png");
        background-repeat: no-repeat, no-repeat;
        background-size: 402px 416px, 463px 463px;
        background-position: 0% -15%, bottom right;
    }


    .report-results-container {
        background-color: white;
        width: 100%;
    }

    .calculation-results {
        font-family: 'Gilroy', sans-serif;
        font-weight: 600;
        font-size: clamp(21, calc(0.71vw + 18.33px), 36px);
        line-height: 100%;
        letter-spacing: 0%;
        color: rgba(57, 49, 133, 1);
    }

    .text-grey-inter {
        font-family: 'Inter', sans-serif;
        font-weight: 400;
        font-size: 16px;
        line-height: 100%;
        letter-spacing: 0%;
        color: rgba(128, 128, 128, 1);
    }

    .text-blue-gilroy {
        font-family: 'Gilroy', sans-serif;
        font-weight: 600;
        font-style: Semibold;
        font-size: 24px;
        line-height: 100%;
        letter-spacing: 0%;
        color: rgba(57, 49, 133, 1);
    }

    .Design {
        width: 210px;
        padding-top: 15px;
        padding-bottom: 15px;
        font-family: 'Inter', sans-serif;
        font-weight: 400;
        font-size: 16px;
        line-height: 100%;
        letter-spacing: 0%;
        text-align: center;

    }

    .dannie-tex-pasporta {
        font-family: 'Gilroy', sans-serif;
        font-weight: 600;
        font-style: Semibold;
        font-size: clamp(18px, calc(0.38vw + 16.54px), 24px);
        line-height: 100%;
        letter-spacing: 0%;
        color: rgba(58, 58, 58, 1);
    }

    .gos-number-header {
        font-family: 'Gilroy', sans-serif;
        font-weight: 500;
        font-style: Medium;
        font-size: 16px;
        line-height: 100%;
        letter-spacing: 0%;
        color: rgba(174, 167, 236, 1);
    }

    .text-information {
        border: none;
        border-bottom: 1px solid lightgrey;
    }

    ::placeholder {
        font-family: 'Gilroy', sans-serif;
        font-weight: 600;
        font-style: Semibold;
        font-size: 18px;
        line-height: 100%;
        letter-spacing: 0%;
        color: rgba(128, 128, 128, 1);
    }

    input[type="text"],
    input[type="tel"] {
        width: 100%;
        font-family: 'Gilroy', sans-serif;
        font-weight: 600;
        font-style: Semibold;
        font-size: 18px;
        line-height: 100%;
        letter-spacing: 0%;
        background-color: rgba(255, 255, 255, 0);
    }

    .file-input-wrapper {
        position: relative;
        cursor: pointer;
    }

    .file-input-button {
        background-color: rgba(57, 49, 133, 1);
        color: white;
        padding: 10px 20px;
        font-size: 16px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .file-input {
        position: absolute;
        left: 0;
        top: 0;
        right: 0;
        bottom: 0;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }

    .btn-plus {
        font-size: 32px;
    }

    .question {
        background-image: url("./img/question\(2\)1.png");
        background-repeat: no-repeat;
        background-size: 20px 20px;
        background-position: right center;
    }

    .back {
        background-color: white;
        border: none;
        padding-bottom: 5px;
        border-bottom: 1px solid darkblue;
    }

    .wrapper-information {
        max-width: 1300px;
        margin-left: auto;
        margin-right: auto;
        background-color: white;
    }

    .data {
        font-family: 'Gilroy', sans-serif;
        font-weight: 600;
        font-style: Semibold;
        font-size: 24px;
        line-height: 100%;
        letter-spacing: 0%;
        color: rgba(58, 58, 58, 1);
    }

    .payment-method {
        font-family: 'Gilroy', sans-serif;
        font-weight: 600;
        font-style: Semibold;
        font-size: 24px;
        line-height: 100%;
        letter-spacing: 0%;
        color: rgba(58, 58, 58, 1);
    }

    .payment-container {
        overflow: auto;
        gap: 57px;
    }

    .payme {
        width: 130px;
        height: auto;
    }

    .visa {
        width: 115px;
        height: auto;
    }

    .btn-pay {
        width: 210px;
        padding: 15px 0;
    }

    #form-content {
        display: none;
    }

    t @keyframes exampe {
        from {
            transform: rotateX(90deg);
        }

        to {
            transform: rotateX(0deg);
        }
    }



    @media(max-width:1000px) {


        .nav-mobile {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .mobile-header-top-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }



        .banner1 {
            background-position: center top, right top;
        }
    }



    @media(max-width:722px) {

        .menu-left,
        .menu-right {
            flex-wrap: wrap;
            width: 100%;
            position: static;
        }

        #menu {
            overflow: auto;
        }

        .fa-close {
            float: right;
            margin-right: 20px;
        }

        @keyframes left-menu {
            from {
                transform: translateY(-200%);
            }

            to {
                transform: translateY(0%);
            }
        }

        @keyframes right-menu {
            from {
                transform: translateY(-400%);
            }

            to {
                transform: translateY(0%);
            }
        }


    }
</style>

<!--
<script>
    window.addEventListener("load", () => {
        var circle = document.getElementsByClassName("circle");
        var line1 = document.getElementsByClassName("line1");
        var line2 = document.getElementsByClassName("line2");
        var line3 = document.getElementsByClassName("line3");
        var line4 = document.getElementsByClassName("line4");
        circle[0].style.backgroundColor = "rgba(57, 49, 133, 1)";
        circle[0].style.color = "white";
        circle[1].style.backgroundColor = "white";
        circle[1].style.color = "rgba(57, 49, 133, 1)";
        circle[2].style.backgroundColor = "white";
        circle[2].style.color = "rgba(57, 49, 133, 1)";
        line1[0].style.border = "1px solid rgba(57, 49, 133, 1)";
        document.getElementById("main-content-1").style.display = "flex";
    });
</script>-->
