<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>SEMESTA</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap");

        body {
            margin: 0;
            padding: 0;
            width: 100%;
            background: #f5f5f5;
            font-family: "Poppins", sans-serif;
        }

        .table {
            width: 100%;
            background: #ffffff;
        }

        .table .center {
            text-align: center;
        }

        .table .logo {
            width: 175px;
            height: 100%;
            -o-object-fit: contain;
            object-fit: contain;
            text-align: center;
            margin-top: 40px;
            margin-bottom: 12px;
        }

        .table .title {
            font-style: normal;
            font-weight: 600;
            font-size: 14px;
            line-height: 20px;
            color: #2a2a2c;
            margin-bottom: 12px;
        }

        .table .email-img {
            width: 340px;
            height: 100%;
            -o-object-fit: contain;
            object-fit: contain;
            margin-bottom: 12px;
        }

        .table .btn-cta {
            padding: 12px;
            padding-left: 50px;
            padding-right: 50px;
            background: #f58220;
            border-radius: 50px;
            font-style: normal;
            font-weight: 600;
            font-size: 12px;
            line-height: 18px;
            color: #ffffff;
            text-decoration: none;
        }

        .table .subtitle {
            font-weight: 400;
            font-size: 12px;
            line-height: 18px;
            text-align: center;
            color: #000000;
        }

        .table .divider {
            border-bottom: 1px solid #e0e4e7;
        }
    </style>
</head>
<body>
<table class="table">
    <tbody>
    <tr>
        <td class="center">
            <img class="logo" src="{{ asset('assets/email/logo.png') }}" alt="email"/>
        </td>
    </tr>
    <tr>
        <td class="center">
            <div class="title">
                Selamat! Anda Telah Terdaftar di Beasiswa SEMESTA Batch 6
            </div>
        </td>
    </tr>
    <tr>
        <td class="center">
            <div class="subtitle" style="margin-bottom: 12px">
                {{ $content }}
            </div>
        </td>
    </tr>
    {{--    <tr>--}}
    {{--        <td class="center" style="padding-bottom: 24px">--}}
    {{--            <a href="{{ $url }}" class="btn-cta">Gabung Grup Telegram</a>--}}
    {{--        </td>--}}
    {{--    </tr>--}}
    <tr>
        <td class="center divider" style="border-top: 1px solid #e0e4e7">
            <div class="subtitle" style="padding-top: 12px; padding-bottom: 12px">
                Silakan abaikan email ini jika kamu merasa tidak pernah membuat
                akun maukuliah 🙏🏻
            </div>
        </td>
    </tr>
    <tr>
        <td class="center">
            <div class="title">PT Sentra Vidya Utama</div>
        </td>
    </tr>
    <tr>
        <td class="center">
            <div class="subtitle" style="margin-bottom: 20px">
                © 2004-2024 PT Sentra Vidya Utama. <br/>
                Hak cipta dilindungi Undang-Undang.
            </div>
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
