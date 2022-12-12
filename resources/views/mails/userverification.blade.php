<!doctype html>
<html>
    <head>
        <meta name="viewport" content="width=device-width" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>User Verify Email Address | KIDI</title>
        <style>
            /* -------------------------------------
            GLOBAL RESETS
            ------------------------------------- */
            /*All the styling goes here*/
          /*  img {
            border: none;
            -ms-interpolation-mode: bicubic;
            max-width: 100%; 
            }*/
            body {
            background-color: #f6f6f6;
            font-family: sans-serif;
            -webkit-font-smoothing: antialiased;
            font-size: 14px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%; 
            }
            table {
            border-collapse: separate;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
            width: 100%; }
            table td {
            font-family: sans-serif;
            font-size: 14px;
            vertical-align: top; 
            }
            /* -------------------------------------
            BODY & CONTAINER
            ------------------------------------- */
            .body {
            background-color: #f6f6f6;
            width: 100%; 
            }
            /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
            .container {
            display: block;
            margin: 0 auto !important;
            /* makes it centered */
            max-width: 580px;
            padding: 10px;
            width: 580px; 
            }
          
            .footer td,
            .footer p,
            .footer span,
            .footer a {
            color: #999999;
            font-size: 12px;
            text-align: center; 
            }
           

            }
        </style>
    </head>
    <body>
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
            <tr>
                <td>&nbsp;</td>
                <td class="container">
                    <div class="content">
                        <!-- START CENTERED WHITE CONTAINER -->
                        <table role="presentation" class="main">
                            <!-- START MAIN CONTENT AREA -->
                            <tr>
                                <td class="wrapper">
                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                        <tr >
                                            <td>
                                                <p class="font-4x">Hey {{$name}},</p>
                                                <p>Thankyou for registering. Please enter below OTP to verify your email address.</p>
                                                <hr style="margin-top:0px;">
                                                <div align="center" style="font-size:24px;">
                                                    <p><b>{{$otp}}</b></p>
                                                </div>
                                                <br>
                                                <p>Warm Regards!</p>
                                                <p>Kidi</p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <!-- END MAIN CONTENT AREA -->
                        </table>
                        <!-- START FOOTER -->
                        <div class="footer">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="content-block1">
                                        <div style="font-size:14px; color:#888888; font-weight:normal; text-align:center; font-family:Arial,Helvetica,sans-serif;">
                                            This email was sent to {{ $email }}
                                            <div>You received this email because you are registered with Kidi.</div>
                                            <div>&nbsp;</div>
                                        </div>
                                        <div style="display: block; text-align: center;">
                                            <span style="font-size:14px; font-weight:normal; display: inline-block; text-align:center; font-family:Arial,Helvetica,sans-serif;">
                                            <a style="text-decoration:underline; color:#666666;font-size:14px;font-weight:normal;font-family:Arial,Helvetica,sans-serif;" target="_blank" href="[UNSUBSCRIBE]">Unsubscribe here</a></span>
                                        </div>
                                    </td>
                                </tr>
                                <!-- <tr>
                                    <td class="content-block powered-by">
                                        <img width="86" src="https://local-kidiapp.s3-us-west-1.amazonaws.com/static/admin/img/Icon-108%402x.png" alt="Kidi">
                                    </td>
                                </tr> -->
                            </table>
                        </div>
                        <!-- END FOOTER -->
                    </div>
                </td>
                <td>&nbsp;</td>
            </tr>
        </table>
    </body>
</html>