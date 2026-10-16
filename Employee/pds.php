<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script defer src="assets/js/bootstrap.min.js"></script>
    <script defer src="assets/js/script.js"></script>
    <title>Personal Data Sheet</title>

</head>

<body>
    <div class="container-fluid">
        <nav class="navbar navbar-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">
                    <img src="https://getbootstrap.com//docs/5.0/assets/brand/bootstrap-logo.svg" alt="" width="30"
                        height="24" class="d-inline-block align-text-top">
                    Personal Data Sheet
                </a>
            </div>
        </nav>
    </div>

    <div class="container">
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home"
                    type="button" role="tab" aria-controls="nav-home" aria-selected="true">C1</button>
                <button class="nav-link" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile"
                    type="button" role="tab" aria-controls="nav-profile" aria-selected="false">C2</button>
                <button class="nav-link" id="nav-contact-tab" data-bs-toggle="tab" data-bs-target="#nav-contact"
                    type="button" role="tab" aria-controls="nav-contact" aria-selected="false">C3</button>
                <button class="nav-link" id="nav-contact2-tab" data-bs-toggle="tab" data-bs-target="#nav-contact2"
                    type="button" role="tab" aria-controls="nav-contact2" aria-selected="false">C4</button>
            </div>
        </nav>

        <div class="tab-content" id="nav-tabContent">
            <!-- START WHOLE C1 -->
            <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">

                <div class="c1-container">

                    <div class="c1-container-header border-bottom">
                        <span>CS Form No. 212</span>
                        <br>
                        <span>Revised 2017</span>
                        <br>
                        <br>
                        <center>
                            <h1>PERSONAL DATA SHEET</h1>
                        </center>
                        <br>
                        <p><b>
                                WARNING: Any misrepresentation made in the Personal Data Sheet and the Work Experience
                                Sheet shall cause the filing of administrative/criminal case/s against the person
                                concerned.
                                <br>
                                READ THE ATTACHED GUIDE TO FILLING OUT THE PERSONAL DATA SHEET (PDS) BEFORE
                                ACCOMPLISHING THE PDS FORM.
                            </b></p>
                    </div>

                    <br>

                    <div class="c1-container-body">

                        <div class="holder-box-0">
                            <div class="form-group row">
                                <label for="CSID" class="col-sm-2 col-form-label">1. CS ID No.</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" id="CSID" placeholder="CSID">
                                </div>
                            </div>

                            <br>

                            <h5><b> I. PERSONAL INFORMATION</b></h3>
                                <hr>
                                <br>

                                <div class="form-group row">
                                    <label for="" class="col-sm-2 col-form-label">2. SURNAME</label>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" id="Surname" name="Surname"
                                            placeholder="Surname">
                                    </div>
                                </div>

                                <br>

                                <div class="form-group row">
                                    <label for="" class="col-sm-2 col-form-label">FIRST NAME</label>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" id="Firstname" name="Firstname"
                                            placeholder="Firstname">
                                    </div>

                                    <label for="" class="col-sm-2 col-form-label">NAME EXTENSION (JR., SR)</label>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" id="NameExtension"
                                            placeholder="NameExtension">
                                    </div>
                                </div>

                                <br>

                                <div class="form-group row">
                                    <label for="" class="col-sm-2 col-form-label">MIDDLE NAME</label>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" id="MiddleName" name="MiddleName"
                                            placeholder="MiddleName">
                                    </div>
                                </div>
                                <br>

                        </div>


                        <hr>


                        <div class="holder-box-1">
                            <div class="box-1">
                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label">3. DATE OF BIRTH
                                        (mm/dd/yyyy) </label>
                                    <div class="col-sm-8">
                                        <input type="date" class="form-control" id="DateOfBirth"
                                            placeholder="DateOfBirth">
                                    </div>
                                </div>

                                <br>

                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label">4. PLACE OF BIRTH</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="PlaceOfBirth"
                                            placeholder="PlaceOfBirth">
                                    </div>
                                </div>

                                <br>

                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label">5. SEX</label>
                                    <div class="col-sm-8">
                                        <input class="form-check-input" type="radio" name="Sex" id="Male" checked
                                            value="Male">
                                        <label class="form-check-label" for="Male">
                                            Male
                                        </label>

                                        &nbsp;&nbsp;
                                        <input class="form-check-input" type="radio" name="Sex" id="Female"
                                            value="Female">
                                        <label class="form-check-label" for="Female">
                                            Female
                                        </label>
                                    </div>
                                </div>

                                <br>


                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label">6. CIVIL STATUS</label>
                                    <div class="col-sm-8">
                                        <input class="form-check-input" type="radio" name="CivilStatus" id="Single"
                                            value="Single" checked>
                                        <label class="form-check-label" for="Single">
                                            Single
                                        </label>

                                        &nbsp;&nbsp;
                                        <input class="form-check-input" type="radio" name="CivilStatus" id="Married"
                                            value="Married">
                                        <label class="form-check-label" for="Married">
                                            Married
                                        </label>

                                        &nbsp;&nbsp;
                                        <input class="form-check-input" type="radio" name="CivilStatus" id="Widowed"
                                            value="Widowed">
                                        <label class="form-check-label" for="Widowed">
                                            Widowed
                                        </label>

                                        <br>

                                        <input class="form-check-input" type="radio" name="CivilStatus" id="Seperated"
                                            value="Seperated">
                                        <label class="form-check-label" for="Seperated">
                                            Seperated
                                        </label>


                                        &nbsp;&nbsp;
                                        <input class="form-check-input" type="radio" name="CivilStatus" id="Other/s"
                                            value="Other/s">
                                        <label class="form-check-label" for="Other/s">
                                            Other/s
                                        </label>
                                    </div>
                                </div>


                                <br>

                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label">7. HEIGHT (m)</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="Height" placeholder="Height">
                                    </div>
                                </div>


                                <br>

                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label">8. WEIGHT (kg)</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="Weight" placeholder="Weight">
                                    </div>
                                </div>

                                <br>

                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label">9. BLOOD TYPE</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="BloodType" placeholder="BloodType">
                                    </div>
                                </div>


                                <br>

                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label">10. GSIS ID NO.</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="GSIS" placeholder="GSIS">
                                    </div>
                                </div>



                                <br>

                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label">11. PAG-IBIG ID NO.</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="PagIBIG" placeholder="PagIBIG">
                                    </div>
                                </div>


                                <br>

                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label">12. PHILHEALTH NO.</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="PHILHEALTH"
                                            placeholder="PHILHEALTH">
                                    </div>
                                </div>


                                <br>

                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label">13. SSS NO.</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="SSS" placeholder="SSS">
                                    </div>
                                </div>


                                <br>

                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label">14. TIN NO.</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="TIN" placeholder="TIN">
                                    </div>
                                </div>


                                <br>

                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label">15. AGENCY EMPLOYEE NO.</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="AgencyNo" placeholder="AgencyNo">
                                    </div>
                                </div>

                            </div>



                            <div class="box-1" style="margin-left: 1%;">
                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label">16. CITIZENSHIP</label>
                                    <div class="col-sm-8">
                                        <input class="form-check-input" type="radio" name="Citizenship1" id="Filipino"
                                            value="Filipino" checked>
                                        <label class="form-check-label" for="Filipino">
                                            Filipino
                                        </label>

                                        &nbsp;&nbsp;
                                        <input class="form-check-input" type="radio" name="Citizenship1" id="Dual"
                                            value="Dual">
                                        <label class="form-check-label" for="Dual">
                                            Dual Citizenship
                                        </label>
                                    </div>
                                </div>


                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label"></label>
                                    <div class="col-sm-8">
                                        <input class="form-check-input" type="radio" name="Citizenship2" id="Birth"
                                            value="by birth" checked>
                                        <label class="form-check-label" for="Birth">
                                            by birth
                                        </label>

                                        &nbsp;&nbsp;
                                        <input class="form-check-input" type="radio" name="Citizenship2"
                                            id="Naturalization" value="by naturalization">
                                        <label class="form-check-label" for="Naturalization">
                                            by naturalization
                                        </label>

                                        <br>
                                        <br>
                                        <center>Pls. indicate country:</center>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label">If holder of dual citizenship, please
                                        indicate the details.</label>
                                    <div class="col-sm-8">
                                        <br>
                                        <input class="form-control" list="datalistOptions" id="DualCountry"
                                            placeholder="DualCountry">
                                        <datalist id="DualCountry">
                                            <option>select country</option>
                                            <option value="AF">Afghanistan</option>
                                            <option value="AX">Aland Islands</option>
                                            <option value="AL">Albania</option>
                                            <option value="DZ">Algeria</option>
                                            <option value="AS">American Samoa</option>
                                            <option value="AD">Andorra</option>
                                            <option value="AO">Angola</option>
                                            <option value="AI">Anguilla</option>
                                            <option value="AQ">Antarctica</option>
                                            <option value="AG">Antigua and Barbuda</option>
                                            <option value="AR">Argentina</option>
                                            <option value="AM">Armenia</option>
                                            <option value="AW">Aruba</option>
                                            <option value="AU">Australia</option>
                                            <option value="AT">Austria</option>
                                            <option value="AZ">Azerbaijan</option>
                                            <option value="BS">Bahamas</option>
                                            <option value="BH">Bahrain</option>
                                            <option value="BD">Bangladesh</option>
                                            <option value="BB">Barbados</option>
                                            <option value="BY">Belarus</option>
                                            <option value="BE">Belgium</option>
                                            <option value="BZ">Belize</option>
                                            <option value="BJ">Benin</option>
                                            <option value="BM">Bermuda</option>
                                            <option value="BT">Bhutan</option>
                                            <option value="BO">Bolivia</option>
                                            <option value="BQ">Bonaire, Sint Eustatius and Saba</option>
                                            <option value="BA">Bosnia and Herzegovina</option>
                                            <option value="BW">Botswana</option>
                                            <option value="BV">Bouvet Island</option>
                                            <option value="BR">Brazil</option>
                                            <option value="IO">British Indian Ocean Territory</option>
                                            <option value="BN">Brunei Darussalam</option>
                                            <option value="BG">Bulgaria</option>
                                            <option value="BF">Burkina Faso</option>
                                            <option value="BI">Burundi</option>
                                            <option value="KH">Cambodia</option>
                                            <option value="CM">Cameroon</option>
                                            <option value="CA">Canada</option>
                                            <option value="CV">Cape Verde</option>
                                            <option value="KY">Cayman Islands</option>
                                            <option value="CF">Central African Republic</option>
                                            <option value="TD">Chad</option>
                                            <option value="CL">Chile</option>
                                            <option value="CN">China</option>
                                            <option value="CX">Christmas Island</option>
                                            <option value="CC">Cocos (Keeling) Islands</option>
                                            <option value="CO">Colombia</option>
                                            <option value="KM">Comoros</option>
                                            <option value="CG">Congo</option>
                                            <option value="CD">Congo, Democratic Republic of the Congo</option>
                                            <option value="CK">Cook Islands</option>
                                            <option value="CR">Costa Rica</option>
                                            <option value="CI">Cote D'Ivoire</option>
                                            <option value="HR">Croatia</option>
                                            <option value="CU">Cuba</option>
                                            <option value="CW">Curacao</option>
                                            <option value="CY">Cyprus</option>
                                            <option value="CZ">Czech Republic</option>
                                            <option value="DK">Denmark</option>
                                            <option value="DJ">Djibouti</option>
                                            <option value="DM">Dominica</option>
                                            <option value="DO">Dominican Republic</option>
                                            <option value="EC">Ecuador</option>
                                            <option value="EG">Egypt</option>
                                            <option value="SV">El Salvador</option>
                                            <option value="GQ">Equatorial Guinea</option>
                                            <option value="ER">Eritrea</option>
                                            <option value="EE">Estonia</option>
                                            <option value="ET">Ethiopia</option>
                                            <option value="FK">Falkland Islands (Malvinas)</option>
                                            <option value="FO">Faroe Islands</option>
                                            <option value="FJ">Fiji</option>
                                            <option value="FI">Finland</option>
                                            <option value="FR">France</option>
                                            <option value="GF">French Guiana</option>
                                            <option value="PF">French Polynesia</option>
                                            <option value="TF">French Southern Territories</option>
                                            <option value="GA">Gabon</option>
                                            <option value="GM">Gambia</option>
                                            <option value="GE">Georgia</option>
                                            <option value="DE">Germany</option>
                                            <option value="GH">Ghana</option>
                                            <option value="GI">Gibraltar</option>
                                            <option value="GR">Greece</option>
                                            <option value="GL">Greenland</option>
                                            <option value="GD">Grenada</option>
                                            <option value="GP">Guadeloupe</option>
                                            <option value="GU">Guam</option>
                                            <option value="GT">Guatemala</option>
                                            <option value="GG">Guernsey</option>
                                            <option value="GN">Guinea</option>
                                            <option value="GW">Guinea-Bissau</option>
                                            <option value="GY">Guyana</option>
                                            <option value="HT">Haiti</option>
                                            <option value="HM">Heard Island and Mcdonald Islands</option>
                                            <option value="VA">Holy See (Vatican City State)</option>
                                            <option value="HN">Honduras</option>
                                            <option value="HK">Hong Kong</option>
                                            <option value="HU">Hungary</option>
                                            <option value="IS">Iceland</option>
                                            <option value="IN">India</option>
                                            <option value="ID">Indonesia</option>
                                            <option value="IR">Iran, Islamic Republic of</option>
                                            <option value="IQ">Iraq</option>
                                            <option value="IE">Ireland</option>
                                            <option value="IM">Isle of Man</option>
                                            <option value="IL">Israel</option>
                                            <option value="IT">Italy</option>
                                            <option value="JM">Jamaica</option>
                                            <option value="JP">Japan</option>
                                            <option value="JE">Jersey</option>
                                            <option value="JO">Jordan</option>
                                            <option value="KZ">Kazakhstan</option>
                                            <option value="KE">Kenya</option>
                                            <option value="KI">Kiribati</option>
                                            <option value="KP">Korea, Democratic People's Republic of</option>
                                            <option value="KR">Korea, Republic of</option>
                                            <option value="XK">Kosovo</option>
                                            <option value="KW">Kuwait</option>
                                            <option value="KG">Kyrgyzstan</option>
                                            <option value="LA">Lao People's Democratic Republic</option>
                                            <option value="LV">Latvia</option>
                                            <option value="LB">Lebanon</option>
                                            <option value="LS">Lesotho</option>
                                            <option value="LR">Liberia</option>
                                            <option value="LY">Libyan Arab Jamahiriya</option>
                                            <option value="LI">Liechtenstein</option>
                                            <option value="LT">Lithuania</option>
                                            <option value="LU">Luxembourg</option>
                                            <option value="MO">Macao</option>
                                            <option value="MK">Macedonia, the Former Yugoslav Republic of</option>
                                            <option value="MG">Madagascar</option>
                                            <option value="MW">Malawi</option>
                                            <option value="MY">Malaysia</option>
                                            <option value="MV">Maldives</option>
                                            <option value="ML">Mali</option>
                                            <option value="MT">Malta</option>
                                            <option value="MH">Marshall Islands</option>
                                            <option value="MQ">Martinique</option>
                                            <option value="MR">Mauritania</option>
                                            <option value="MU">Mauritius</option>
                                            <option value="YT">Mayotte</option>
                                            <option value="MX">Mexico</option>
                                            <option value="FM">Micronesia, Federated States of</option>
                                            <option value="MD">Moldova, Republic of</option>
                                            <option value="MC">Monaco</option>
                                            <option value="MN">Mongolia</option>
                                            <option value="ME">Montenegro</option>
                                            <option value="MS">Montserrat</option>
                                            <option value="MA">Morocco</option>
                                            <option value="MZ">Mozambique</option>
                                            <option value="MM">Myanmar</option>
                                            <option value="NA">Namibia</option>
                                            <option value="NR">Nauru</option>
                                            <option value="NP">Nepal</option>
                                            <option value="NL">Netherlands</option>
                                            <option value="AN">Netherlands Antilles</option>
                                            <option value="NC">New Caledonia</option>
                                            <option value="NZ">New Zealand</option>
                                            <option value="NI">Nicaragua</option>
                                            <option value="NE">Niger</option>
                                            <option value="NG">Nigeria</option>
                                            <option value="NU">Niue</option>
                                            <option value="NF">Norfolk Island</option>
                                            <option value="MP">Northern Mariana Islands</option>
                                            <option value="NO">Norway</option>
                                            <option value="OM">Oman</option>
                                            <option value="PK">Pakistan</option>
                                            <option value="PW">Palau</option>
                                            <option value="PS">Palestinian Territory, Occupied</option>
                                            <option value="PA">Panama</option>
                                            <option value="PG">Papua New Guinea</option>
                                            <option value="PY">Paraguay</option>
                                            <option value="PE">Peru</option>
                                            <option value="PH">Philippines</option>
                                            <option value="PN">Pitcairn</option>
                                            <option value="PL">Poland</option>
                                            <option value="PT">Portugal</option>
                                            <option value="PR">Puerto Rico</option>
                                            <option value="QA">Qatar</option>
                                            <option value="RE">Reunion</option>
                                            <option value="RO">Romania</option>
                                            <option value="RU">Russian Federation</option>
                                            <option value="RW">Rwanda</option>
                                            <option value="BL">Saint Barthelemy</option>
                                            <option value="SH">Saint Helena</option>
                                            <option value="KN">Saint Kitts and Nevis</option>
                                            <option value="LC">Saint Lucia</option>
                                            <option value="MF">Saint Martin</option>
                                            <option value="PM">Saint Pierre and Miquelon</option>
                                            <option value="VC">Saint Vincent and the Grenadines</option>
                                            <option value="WS">Samoa</option>
                                            <option value="SM">San Marino</option>
                                            <option value="ST">Sao Tome and Principe</option>
                                            <option value="SA">Saudi Arabia</option>
                                            <option value="SN">Senegal</option>
                                            <option value="RS">Serbia</option>
                                            <option value="CS">Serbia and Montenegro</option>
                                            <option value="SC">Seychelles</option>
                                            <option value="SL">Sierra Leone</option>
                                            <option value="SG">Singapore</option>
                                            <option value="SX">Sint Maarten</option>
                                            <option value="SK">Slovakia</option>
                                            <option value="SI">Slovenia</option>
                                            <option value="SB">Solomon Islands</option>
                                            <option value="SO">Somalia</option>
                                            <option value="ZA">South Africa</option>
                                            <option value="GS">South Georgia and the South Sandwich Islands</option>
                                            <option value="SS">South Sudan</option>
                                            <option value="ES">Spain</option>
                                            <option value="LK">Sri Lanka</option>
                                            <option value="SD">Sudan</option>
                                            <option value="SR">Suriname</option>
                                            <option value="SJ">Svalbard and Jan Mayen</option>
                                            <option value="SZ">Swaziland</option>
                                            <option value="SE">Sweden</option>
                                            <option value="CH">Switzerland</option>
                                            <option value="SY">Syrian Arab Republic</option>
                                            <option value="TW">Taiwan, Province of China</option>
                                            <option value="TJ">Tajikistan</option>
                                            <option value="TZ">Tanzania, United Republic of</option>
                                            <option value="TH">Thailand</option>
                                            <option value="TL">Timor-Leste</option>
                                            <option value="TG">Togo</option>
                                            <option value="TK">Tokelau</option>
                                            <option value="TO">Tonga</option>
                                            <option value="TT">Trinidad and Tobago</option>
                                            <option value="TN">Tunisia</option>
                                            <option value="TR">Turkey</option>
                                            <option value="TM">Turkmenistan</option>
                                            <option value="TC">Turks and Caicos Islands</option>
                                            <option value="TV">Tuvalu</option>
                                            <option value="UG">Uganda</option>
                                            <option value="UA">Ukraine</option>
                                            <option value="AE">United Arab Emirates</option>
                                            <option value="GB">United Kingdom</option>
                                            <option value="US">United States</option>
                                            <option value="UM">United States Minor Outlying Islands</option>
                                            <option value="UY">Uruguay</option>
                                            <option value="UZ">Uzbekistan</option>
                                            <option value="VU">Vanuatu</option>
                                            <option value="VE">Venezuela</option>
                                            <option value="VN">Viet Nam</option>
                                            <option value="VG">Virgin Islands, British</option>
                                            <option value="VI">Virgin Islands, U.s.</option>
                                            <option value="WF">Wallis and Futuna</option>
                                            <option value="EH">Western Sahara</option>
                                            <option value="YE">Yemen</option>
                                            <option value="ZM">Zambia</option>
                                            <option value="ZW">Zimbabwe</option>
                                        </datalist>
                                    </div>
                                </div>

                                <hr>

                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label">17. RESIDENTIAL ADDRESS</label>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" id="ResHouse_Block_LotNo"
                                            placeholder="ResHouse_Block_LotNo">
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" id="ResStreet" placeholder="ResStreet">
                                    </div>
                                </div>



                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label"></label>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" id="ResSubdivision_Village"
                                            placeholder="ResSubdivision_Village">
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" id="ResBarangay"
                                            placeholder="ResBarangay">
                                    </div>
                                </div>

                                <br>

                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label"></label>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" id="ResCity_Municipality"
                                            placeholder="ResCity_Municipality">
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" id="ResProvince"
                                            placeholder="ResProvince">
                                    </div>
                                </div>


                                <br>

                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label">
                                        <center>ZIP CODE</center>
                                    </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="ResZipCode"
                                            placeholder="ResZipCode">
                                    </div>
                                </div>


                                <hr>


                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label">18. PERMANENT ADDRESS</label>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" id="PerHouse_Block_LotNo"
                                            placeholder="PerHouse_Block_LotNo">
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" id="PerStreet" placeholder="PerStreet">
                                    </div>
                                </div>



                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label"></label>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" id="PerSubdivision_Village"
                                            placeholder="PerSubdivision_Village">
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" id="PerBarangay"
                                            placeholder="PerBarangay">
                                    </div>
                                </div>

                                <br>

                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label"></label>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" id="PerCity_Municipality"
                                            placeholder="PerCity_Municipality">
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control" id="PerProvince"
                                            placeholder="PerProvince">
                                    </div>
                                </div>


                                <br>

                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label">
                                        <center>ZIP CODE</center>
                                    </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="PerZipCode"
                                            placeholder="PerZipCode">
                                    </div>
                                </div>

                                <hr>

                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label">19. TELEPHONE NO.</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="TelephoneNumber"
                                            placeholder="TelephoneNumber">
                                    </div>
                                </div>

                                <br>

                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label">20. MOBILE NO.</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="MobileNumber"
                                            placeholder="MobileNumber">
                                    </div>
                                </div>

                                <br>

                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-label">21. E-MAIL ADDRESS (if any)</label>
                                    <div class="col-sm-8">
                                        <input type="email" class="form-control" id="EmailAdd" placeholder="EmailAdd">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <br>

                        <h5><b> II. FAMILY BACKGROUND</b></h3>
                            <hr>

                            <div class="holder-box-2">
                                <div class="box-1">
                                    <div class="form-group row">
                                        <label for="" class="col-sm-4 col-form-label">22. SPOUSE'S SURNAME</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="Spouse_Surname"
                                                placeholder="Spouse_Surname">
                                        </div>
                                    </div>

                                    <br>

                                    <div class="form-group row">
                                        <label for="" class="col-sm-4 col-form-label"> FIRST NAME</label>
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control" id="Spouse_Firstname"
                                                placeholder="Spouse_Firstname">
                                        </div>

                                        <label for="" class="col-sm-2 col-form-label"> NAME EXTENSION (JR., SR)</label>
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control" id="Spouse_NameExtension"
                                                placeholder="Spouse_NameExtension">
                                        </div>
                                    </div>

                                    <br>

                                    <div class="form-group row">
                                        <label for="" class="col-sm-4 col-form-label"> MIDDLE NAME</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="Spouse_Middlename"
                                                placeholder="Spouse_Middlename">
                                        </div>
                                    </div>

                                    <br>

                                    <div class="form-group row">
                                        <label for="" class="col-sm-4 col-form-label"> OCCUPATION</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="Spouse_Occupation"
                                                placeholder="Spouse_Occupation">
                                        </div>
                                    </div>

                                    <br>

                                    <div class="form-group row">
                                        <label for="" class="col-sm-4 col-form-label"> EMPLOYER/BUSINESS NAME</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="Spouse_Employer_Businessname"
                                                placeholder="Spouse_Employer_Businessname">
                                        </div>
                                    </div>

                                    <br>

                                    <div class="form-group row">
                                        <label for="" class="col-sm-4 col-form-label"> BUSINESS ADDRESS</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="Spouse_BusinessAddress"
                                                placeholder="Spouse_BusinessAddress">
                                        </div>
                                    </div>

                                    <br>

                                    <div class="form-group row">
                                        <label for="" class="col-sm-4 col-form-label"> TELEPHONE NO.</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="Spouse_TelephoneNumber"
                                                placeholder="Spouse_TelephoneNumber">
                                        </div>
                                    </div>
                                </div>

                                <div class="box-1" style="margin-left: 1%;">
                                    <div class="form-group row">
                                        <label for="" class="col-sm-6 col-form-label">23. NAME of CHILDREN (Write full
                                            name and list all)</label>
                                        <label for="" class="col-sm-6 col-form-label">DATE OF BIRTH (mm/dd/yyyy)
                                        </label>
                                    </div>
                                    <br>
                                    <div class="form-group row">
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" id="Children_Fullname"
                                                placeholder="Children_Fullname">

                                        </div>
                                        <br>
                                        <br>
                                        <div class="col-sm-4">
                                            <input type="date" class="form-control" id="Children_Birthdate"
                                                placeholder="Children_Birthdate">
                                        </div>
                                        <br>
                                        <br>

                                        <div class="col-sm-2">
                                            <input type="button" class="btn btn-dark" id="Spouse-Add-Children"
                                                value="ADD">
                                        </div>
                                    </div>

                                    <br>
                                    <br>

                                    <!-- <ol id="spouse-children" style="list-style-position: inside; padding: 0px;">

                    </ol> -->

                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th scope="col">NAME of CHILDREN</th>
                                                    <th scope="col">DATE OF BIRTH</th>
                                                    <th scope="col">Control</th>
                                                </tr>
                                            </thead>
                                            <tbody id="spouse-children">
                                            </tbody>
                                        </table>
                                    </div>



                                    <br>

                                    <div class="form-group row">
                                        <hr style="margin: 0;">
                                        <label for="" class="col-sm-12 col-form-label"
                                            style="font-size: 0.5em; color: red; font-style: italic;">
                                            <center>(Continue on separate sheet if necessary)</center>
                                        </label>
                                        <hr>
                                    </div>
                                </div>
                            </div>

                            <div class="holder-box-2">
                                <div class="box-1">


                                    <hr>


                                    <div class="form-group row">
                                        <label for="" class="col-sm-4 col-form-label">23. FATHER'S SURNAME</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="Father_Surname"
                                                placeholder="Father_Surname">
                                        </div>
                                    </div>

                                    <br>

                                    <div class="form-group row">
                                        <label for="" class="col-sm-4 col-form-label"> FIRST NAME</label>
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control" id="Father_Firstname"
                                                placeholder="Father_Firstname">
                                        </div>

                                        <label for="" class="col-sm-2 col-form-label"> NAME EXTENSION (JR., SR)</label>
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control" id="Father_NameExtension"
                                                placeholder="Father_NameExtension">
                                        </div>
                                    </div>

                                    <br>

                                    <div class="form-group row">
                                        <label for="" class="col-sm-4 col-form-label">MIDDLE NAME</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="Father_Middlename"
                                                placeholder="Father_Middlename">
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="form-group row">
                                        <label for="" class="col-sm-4 col-form-label">24. MOTHER'S MAIDEN NAME</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="Mother_MaidenName"
                                                placeholder="Mother_MaidenName">
                                        </div>
                                    </div>

                                    <br>

                                    <div class="form-group row">
                                        <label for="" class="col-sm-4 col-form-label">SURNAME</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="Mother_Surname"
                                                placeholder="Mother_Surname">
                                        </div>
                                    </div>

                                    <br>

                                    <div class="form-group row">
                                        <label for="" class="col-sm-4 col-form-label">FIRST NAME</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="Mother_Firstname"
                                                placeholder="Mother_Firstname">
                                        </div>
                                    </div>

                                    <br>

                                    <div class="form-group row">
                                        <label for="" class="col-sm-4 col-form-label">MIDDLE NAME</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="Mother_Middlename"
                                                placeholder="Mother_Middlename">
                                        </div>
                                    </div>

                                </div>

                                <div class="box-1" style="margin-left: 1%;">

                                </div>

                            </div>


                            <br>
                            <br>
                            <h5><b> III. EDUCATIONAL BACKGROUND</b></h3>
                                <hr>

                                <div class="form-group row">
                                    <label for="" class="col-sm-2 col-form-label edu-label">LEVEL</label>
                                    <label for="" class="col-sm-2 col-form-label edu-label">ELEMENTARY</label>
                                    <label for="" class="col-sm-2 col-form-label edu-label">SECONDARY</label>
                                    <label for="" class="col-sm-2 col-form-label edu-label">Vocational COURSE</label>
                                    <label for="" class="col-sm-2 col-form-label edu-label">COLLEGE</label>
                                    <label for="" class="col-sm-2 col-form-label edu-label">GRADUATE STUDIES </label>
                                </div>



                                <div class="form-group row">
                                    <label for="" class="col-sm-2 col-form-label">NAME OF SCHOOL (Write in full)</label>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="NOF_Elementary"
                                            placeholder="NOF_Elementary">
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="NOF_Secondary"
                                            placeholder="NOF_Secondary">
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="NOF_Vocational"
                                            placeholder="NOF_Vocational">
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="NOF_College"
                                            placeholder="NOF_College">
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="NOF_Graduate"
                                            placeholder="NOF_Graduate">
                                    </div>
                                    <br>
                                    <br>
                                    <br>
                                    <hr>
                                    <br>
                                </div>

                                <div class="form-group row">
                                    <label for="" class="col-sm-2 col-form-label">BASIC EDUCATION / DEGREE/COURSE (Write
                                        in full) </label>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="BE_D_C_Elementary"
                                            placeholder="BE_D_C_Elementary">
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="BE_D_C_Secondary"
                                            placeholder="BE_D_C_Secondary">
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="BE_D_C_Vocational"
                                            placeholder="BE_D_C_Vocational">
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="BE_D_C_College"
                                            placeholder="BE_D_C_College">
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="BE_D_C_Graduate"
                                            placeholder="BE_D_C_Graduate">
                                    </div>
                                    <br>
                                    <br>
                                    <br>
                                    <hr>
                                    <br>

                                </div>


                                <div class="form-group row">
                                    <br>
                                    <br>
                                    <label for="" class="col-sm-2 col-form-label">PERIOD OF ATTENDANCE</label>

                                    <div class="col-sm-2">
                                        <label for="">ELEMENTARY</label>
                                        <input type="text" class="form-control" id="POA_From_Elementary"
                                            placeholder="POA_From_Elementary">
                                        <input type="text" class="form-control" id="POA_To_Elementary"
                                            placeholder="POA_To_Elementary">
                                        <br>
                                    </div>

                                    <div class="col-sm-2">
                                        <label for="">SECONDARY</label>
                                        <input type="text" class="form-control" id="POA_From_Secondary"
                                            placeholder="POA_From_Secondary">
                                        <input type="text" class="form-control" id="POA_To_Secondary"
                                            placeholder="POA_To_Secondary">
                                        <br>
                                    </div>

                                    <div class="col-sm-2">
                                        <label for="">Vocational COURSE</label>
                                        <input type="text" class="form-control" id="POA_From_Vocational"
                                            placeholder="POA_From_Vocational">
                                        <input type="text" class="form-control" id="POA_To_Vocational"
                                            placeholder="POA_To_Vocational">
                                        <br>
                                    </div>

                                    <div class="col-sm-2">
                                        <label for="">COLLEGE</label>
                                        <input type="text" class="form-control" id="POA_From_College"
                                            placeholder="POA_From_College">
                                        <input type="text" class="form-control" id="POA_To_College"
                                            placeholder="POA_To_College">
                                        <br>
                                    </div>

                                    <div class="col-sm-2">
                                        <label for="">GRADUATE STUDIES</label>
                                        <input type="text" class="form-control" id="POA_From_Graduate"
                                            placeholder="POA_From_Graduate">
                                        <input type="text" class="form-control" id="POA_To_Graduate"
                                            placeholder="POA_To_Graduate">
                                        <br>
                                    </div>
                                    <hr>
                                </div>


                                <div class="form-group row">
                                    <label for="" class="col-sm-2 col-form-label">HIGHEST LEVEL / UNITS EARNED (if not
                                        graduated)</label>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="HL_UE_Elementary"
                                            placeholder="HL_UE_Elementary">
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="HL_UE_Secondary"
                                            placeholder="HL_UE_Secondary">
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="HL_UE_Vocational"
                                            placeholder="HL_UE_Vocational">
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="HL_UE_College"
                                            placeholder="HL_UE_College">
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="HL_UE_Graduate"
                                            placeholder="HL_UE_Graduate">
                                    </div>
                                    <br>
                                    <br>
                                    <hr>
                                    <br>
                                </div>


                                <div class="form-group row">
                                    <label for="" class="col-sm-2 col-form-label">YEAR GRADUATED</label>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="YR_G_Elementary"
                                            placeholder="YR_G_Elementary">
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="YR_G_Secondary"
                                            placeholder="YR_G_Secondary">
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="YR_G_Vocational"
                                            placeholder="YR_G_Vocational">
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="YR_G_College"
                                            placeholder="YR_G_College">
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="YR_G_Graduate"
                                            placeholder="YR_G_Graduate">
                                    </div>
                                    <br>
                                    <br>
                                    <hr>
                                    <br>
                                </div>


                                <div class="form-group row">
                                    <label for="" class="col-sm-2 col-form-label">SCHOLARSHIP/ ACADEMIC HONORS
                                        RECEIVED</label>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="Scho_AHR_Elementary"
                                            placeholder="Scho_AHR_Elementary">
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="Scho_AHR_Secondary"
                                            placeholder="Scho_AHR_Secondary">
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="Scho_AHR_Vocational"
                                            placeholder="Scho_AHR_Vocational">
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="Scho_AHR_College"
                                            placeholder="Scho_AHR_College">
                                    </div>
                                    <div class="col-sm-2">
                                        <input type="text" class="form-control" id="Scho_AHR_Graduate"
                                            placeholder="Scho_AHR_Graduate">
                                    </div>
                                    <br>
                                    <br>
                                    <br>
                                    <hr style="margin: 0;">
                                    <label for="" class="col-sm-12 col-form-label"
                                        style="font-size: 0.5em; color: red; font-style: italic;">
                                        <center>(Continue on separate sheet if necessary)</center>
                                    </label>
                                    <hr>
                                </div>

                                <br>



                                <!-- <div class="form-group row">
                  <label for="" class="col-sm-1 col-form-label">SIGNATURE</label>
                  <div class="col-sm-5">
                    <input type="file" class="form-control" id="C1_Signature" placeholder="C1_Signature" name="C1_Signature">
                  </div>

                  <label for="" class="col-sm-1 col-form-label">DATE</label>
                  <div class="col-sm-5">
                    <input type="date" class="form-control" id="C1_Date" placeholder="C1_Date" name="C1_Date">
                  </div>
                </div> -->

                    </div>


                </div>


            </div>
            <!-- END WHOLE C1 -->


            <!-- START WHOLE C2 -->
            <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                <div class="c2-container">

                    <div class="c2-container-header">

                    </div>

                    <div class="c2-container-body">

                        <br>

                        <h5><b>IV. CIVIL SERVICE ELIGIBILITY</b></h3>
                            <hr class="inputting-0">
                            <br class="inputting-0">

                            <div class="form-group row">
                                <label for="" class="col-sm-1 col-form-label inputting-0">27.</label>
                                <label for="" class="col-sm-3 col-form-label border-end inputting-0 text-center">
                                    CAREER SERVICE/ RA 1080 (BOARD/ BAR) UNDER SPECIAL LAWS/ CES/ CSEE BARANGAY
                                    ELIGIBILITY / DRIVER'S LICENSE
                                </label>
                                <label for="" class="col-sm-1 col-form-label border-end inputting-0 text-center">
                                    RATING
                                    (If Applicable)
                                </label>
                                <label for="" class="col-sm-1 col-form-label border-end inputting-0 text-center">
                                    DATE OF EXAMINATION / CONFERMENT
                                </label>
                                <label for="" class="col-sm-3 col-form-label border-end inputting-0 text-center">
                                    PLACE OF EXAMINATION / CONFERMENT
                                </label>
                                <label for="" class="col-sm-3 col-form-label inputting-0 text-center">
                                    LICENSE (if applicable)
                                    <hr>
                                    <div class="form-group row">
                                        <label for=""
                                            class="col-sm-6 col-form-label border-end inputting-0 text-center">NUMBER</label>
                                        <label for="" class="col-sm-6 col-form-label inputting-0 text-center">Date of
                                            Validity</label>
                                    </div>
                                </label>
                            </div>

                            <br>

                            <div class="form-group row border-top">
                                <div class="col-sm-4">
                                    <br>
                                    <label class="inputting" for="">27. CAREER SERVICE/ RA 1080 (BOARD/ BAR) UNDER
                                        SPECIAL LAWS/ CES/ CSEE BARANGAY ELIGIBILITY / DRIVER'S LICENSE</label>
                                    <input type="text" class="form-control" id="CS_RA_CES_CSEE_DL"
                                        placeholder="CS_RA_CES_CSEE_DL">
                                </div>

                                <div class="col-sm-1">
                                    <br>
                                    <label class="inputting" for="">RATING (If Applicable)</label>
                                    <input type="text" class="form-control" id="Rating" placeholder="Rating">
                                </div>

                                <div class="col-sm-1">
                                    <br>
                                    <label class="inputting" for="">DATE OF EXAMINATION / CONFERMENT</label>
                                    <input type="text" class="form-control" id="DOT_C" placeholder="DOT_C">
                                </div>

                                <div class="col-sm-3">
                                    <br>
                                    <label class="inputting" for="">PLACE OF EXAMINATION / CONFERMENT</label>
                                    <input type="text" class="form-control" id="POE_C" placeholder="POE_C">
                                </div>

                                <div class="col-sm-3">
                                    <br>
                                    <div class="form-group row">
                                        <div class="col-sm-6">
                                            <label class="inputting" for="">LICENSE (if applicable) NUMBER</label>
                                            <input type="text" class="form-control" id="LNumber" placeholder="LNumber">
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="inputting" for="">LICENSE (if applicable) Date of
                                                Validity</label>
                                            <input type="text" class="form-control" id="LValidity"
                                                placeholder="LValidity">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <br>

                            <div class="form-group row">
                                <div class="col-sm-2">
                                    <input type="button" class="btn btn-dark" id="CivilServiceAdd" value="ADD">
                                </div>

                                <br>
                                <br>

                                <!-- <ol id="civil-service" style="list-style-position: inside;">

                  </ol> -->

                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th scope="col">CAREER SERVICE/ RA 1080 (BOARD/ BAR) UNDER SPECIAL LAWS/
                                                    CES/ CSEE BARANGAY ELIGIBILITY / DRIVER'S LICENSE</th>
                                                <th scope="col">RATING</th>
                                                <th scope="col">DATE OF EXAMINATION / CONFERMENT</th>
                                                <th scope="col">PLACE OF EXAMINATION / CONFERMENT</th>
                                                <th scope="col">LICENSE NUMBER</th>
                                                <th scope="col">LICENSE Date of Validity</th>
                                                <th scope="col">Control</th>
                                            </tr>
                                        </thead>
                                        <tbody id="civil-service">

                                        </tbody>
                                    </table>
                                </div>


                                <br>
                                <br>
                                <br>
                                <hr style="margin: 0;">
                                <label for="" class="col-sm-12 col-form-label"
                                    style="font-size: 0.5em; color: red; font-style: italic;">
                                    <center>(Continue on separate sheet if necessary)</center>
                                </label>
                                <hr>
                            </div>





                            <h5><b>V. WORK EXPERIENCE </b></h3>
                                <hr class="inputting-0">
                                <br class="inputting-0">

                                <div class="form-group row">

                                    <label for="" class="col-sm-1 inputting-0">
                                        28.
                                    </label>

                                    <label class="col-sm-3 text-center border-end">
                                        <label class="inputting-0" for="">INCLUSIVE DATES <br> (mm/dd/yyyy)</label>
                                        <hr>
                                        <div class="form-group row">
                                            <div class="col-sm-6 border-end">
                                                <label class="inputting-0">From</label>
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="inputting-0">To</label>
                                            </div>
                                        </div>
                                    </label>

                                    <label class="col-sm-2 border-end inputting-0 text-center">
                                        POSITION TITLE <br> (Write in full/Do not abbreviate)
                                    </label>

                                    <label for="" class="col-sm-2 border-end inputting-0 text-center">
                                        DEPARTMENT / AGENCY / OFFICE / COMPANY <br> (Write in full/Do not abbreviate)
                                    </label>


                                    <label for="" class="col-sm-1 border-end inputting-0 text-center">
                                        MONTHLY SALARY
                                    </label>


                                    <label for="" class="col-sm-1 border-end inputting-0 text-center">
                                        SALARY/ JOB/ PAY GRADE (if applicable)& STEP (Format "00-0")/ INCREMENT
                                    </label>

                                    <label for="" class="col-sm-1 border-end inputting-0 text-center">
                                        STATUS OF APPOINTMENT
                                    </label>

                                    <label for="" class="col-sm-1 inputting-0 text-center">
                                        GOV'T SERVICE (Y/ N)
                                    </label>

                                </div>

                                <hr class="inputting-0">

                                <div class="form-group row">

                                    <div class="col-sm-1">

                                    </div>

                                    <div class="col-sm-3">
                                        <div class="form-group row">
                                            <div class="col-sm-6">
                                                <br>
                                                <label class="inputting" for="">28. INCLUSIVE DATES <br> (mm/dd/yyyy)
                                                    From</label>
                                                <input type="date" class="form-control" id="V_I_D_From"
                                                    placeholder="V_I_D_From">
                                            </div>
                                            <div class="col-sm-6">
                                                <br>
                                                <label class="inputting" for="">INCLUSIVE DATES <br> (mm/dd/yyyy)
                                                    To</label>
                                                <input type="date" class="form-control" id="V_I_D_To"
                                                    placeholder="V_I_D_To">
                                            </div>
                                        </div>
                                    </div>


                                    <div class="col-sm-2">
                                        <br>
                                        <label class="inputting" for="">POSITION TITLE <br> (Write in full/Do not
                                            abbreviate)</label>
                                        <input type="text" class="form-control" id="V_PosTitle"
                                            placeholder="V_PosTitle">
                                    </div>

                                    <div class="col-sm-2">
                                        <br>
                                        <label class="inputting" for="">DEPARTMENT / AGENCY / OFFICE / COMPANY <br>
                                            (Write in full/Do not abbreviate)</label>
                                        <input type="text" class="form-control" id="V_D_A_O_C" placeholder="V_D_A_O_C">
                                    </div>

                                    <div class="col-sm-1">
                                        <br>
                                        <label class="inputting" for="">MONTHLY SALARY</label>
                                        <input type="text" class="form-control" id="V_MS" placeholder="V_MS">
                                    </div>

                                    <div class="col-sm-1">
                                        <br>
                                        <label class="inputting" for="">SALARY/ JOB/ PAY GRADE (if applicable) <br> &
                                            STEP (Format "00-0") / INCREMENT</label>
                                        <input type="text" class="form-control" id="V_S_J_PG" placeholder="V_S_J_PG">
                                    </div>

                                    <div class="col-sm-1">
                                        <br>
                                        <label class="inputting" for="">STATUS OF APPOINTMENT</label>
                                        <input type="text" class="form-control" id="V_SOA" placeholder="V_SOA">
                                    </div>

                                    <div class="col-sm-1">
                                        <br>
                                        <label class="inputting" for="">GOV'T SERVICE (Y/ N)</label>
                                        <input type="text" class="form-control" id="V_GOS" placeholder="V_GOS">
                                    </div>

                                </div>

                                <br>

                                <div class="form-group row">
                                    <div class="col-sm-2">
                                        <input type="button" class="btn btn-dark" id="WorkExperienceAdd" value="ADD">
                                    </div>

                                    <br>
                                    <br>

                                    <!-- <ol id="work-experience" style="list-style-position: inside;">

                  </ol> -->

                                    <br>

                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th scope="col">INCLUSIVE DATES From</th>
                                                    <th scope="col">INCLUSIVE DATES To</th>
                                                    <th scope="col">POSITION TITLE</th>
                                                    <th scope="col">DEPARTMENT / AGENCY / OFFICE / COMPANY</th>
                                                    <th scope="col">MONTHLY SALARY</th>
                                                    <th scope="col">SALARY/ JOB / PAY GRADE / INCREMENT</th>
                                                    <th scope="col">STATUS OF APPOINTMENT</th>
                                                    <th scope="col">GOV'T SERVICE (Y/ N)</th>
                                                    <th scope="col">Control</th>
                                                </tr>
                                            </thead>
                                            <tbody id="work-experience">
                                            </tbody>
                                        </table>
                                    </div>

                                    <br>
                                    <br>
                                    <br>
                                    <hr style="margin: 0;">
                                    <label for="" class="col-sm-12 col-form-label"
                                        style="font-size: 0.5em; color: red; font-style: italic;">
                                        <center>(Continue on separate sheet if necessary)</center>
                                    </label>
                                    <hr>
                                </div>

                                <br>

                                <!-- <div class="form-group row">
                  <label for="" class="col-sm-1 col-form-label">SIGNATURE</label>
                  <div class="col-sm-5">
                    <input type="file" class="form-control" id="C2_Signature" placeholder="C2_Signature" name=C2_Signature>
                  </div>

                  <label for="" class="col-sm-1 col-form-label">DATE</label>
                  <div class="col-sm-5">
                    <input type="date" class="form-control" id="C2_Date" placeholder="C2_Date" name="C2_Date">
                  </div>
                </div> -->


                    </div>
                </div>
            </div>
            <!-- END WHOLE C2 -->


            <!-- START WHOLE C3 -->
            <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">
                <div class="c3-container">
                    <div class="c3-container-header"></div>
                    <div class="c3-container-body">

                        <br>

                        <h5><b>VI. VOLUNTARY WORK OR INVOLVEMENT IN CIVIC / NON-GOVERNMENT / PEOPLE / VOLUNTARY
                                ORGANIZATION/S</b></h3>
                            <hr class="inputting-0">
                            <br class="inputting-0">

                            <div class="form-group row">
                                <label for="" class="col-sm-1 col-form-label inputting-0">29.</label>
                                <label for="" class="col-sm-3 col-form-label border-end inputting-0 text-center">
                                    NAME & ADDRESS OF ORGANIZATION <br> (Write in full)
                                </label>
                                <label for="" class="col-sm-3 col-form-label inputting-0 border-end text-center">
                                    INCLUSIVE DATES <br> (mm/dd/yyyy)
                                    <hr>
                                    <div class="form-group row">
                                        <label for=""
                                            class="col-sm-6 col-form-label border-end inputting-0 text-center">From</label>
                                        <label for="" class="col-sm-6 col-form-label inputting-0 text-center">To</label>
                                    </div>
                                </label>
                                <label for="" class="col-sm-1 col-form-label border-end inputting-0 text-center">
                                    NUMBER OF HOURS
                                </label>
                                <label for="" class="col-sm-4 col-form-label border-end inputting-0 text-center">
                                    POSITION / NATURE OF WORK
                                </label>
                            </div>

                            <br>

                            <div class="form-group row border-top">
                                <div class="col-sm-4">
                                    <br>
                                    <label class="inputting" for="">NAME & ADDRESS OF ORGANIZATION <br> (Write in
                                        full)</label>
                                    <input type="text" class="form-control" id="NAOF" placeholder="NAOF">
                                </div>


                                <div class="col-sm-3">
                                    <br>
                                    <div class="form-group row">
                                        <div class="col-sm-6">
                                            <label class="inputting" for="">INCLUSIVE DATES <br> (mm/dd/yyyy)
                                                From</label>
                                            <input type="text" class="form-control" id="VI_I_D_From"
                                                placeholder="VI_I_D_From">
                                        </div>
                                        <div class="col-sm-6">
                                            <label class="inputting" for="">INCLUSIVE DATES <br> (mm/dd/yyyy) To</label>
                                            <input type="text" class="form-control" id="VI_I_D_To"
                                                placeholder="VI_I_D_To">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-1">
                                    <br>
                                    <label class="inputting" for="">NUMBER OF HOURS</label>
                                    <input type="text" class="form-control" id="VI_NOF" placeholder="VI_NOF">
                                </div>

                                <div class="col-sm-4">
                                    <br>
                                    <label class="inputting" for="">POSITION / NATURE OF WORK</label>
                                    <input type="text" class="form-control" id="VI_POS_NOW" placeholder="VI_POS_NOW">
                                </div>
                            </div>

                            <br>

                            <div class="form-group row">
                                <div class="col-sm-2">
                                    <input type="button" class="btn btn-dark" id="VoluntaryAdd" value="ADD">
                                </div>

                                <br>
                                <br>
                                <br>

                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th scope="col">NAME & ADDRESS OF ORGANIZATION</th>
                                                <th scope="col">INCLUSIVE DATES FROM</th>
                                                <th scope="col">INCLUSIVE DATES TO</th>
                                                <th scope="col">NUMBER OF HOURS</th>
                                                <th scope="col">POSITION / NATURE OF WORK</th>
                                                <th scope="col">Control</th>
                                            </tr>
                                        </thead>
                                        <tbody id="voluntary-work">

                                        </tbody>
                                    </table>
                                </div>

                                <br>
                                <br>
                                <hr style="margin: 0;">
                                <label for="" class="col-sm-12 col-form-label"
                                    style="font-size: 0.5em; color: red; font-style: italic;">
                                    <center>(Continue on separate sheet if necessary)</center>
                                </label>
                                <hr>
                            </div>

                            <br>

                            <h5><b>VII. LEARNING AND DEVELOPMENT (L&D) INTERVENTIONS/TRAINING PROGRAMS ATTENDED</b></h3>
                                <h6>(Start from the most recent L&D/training program and include only the relevant
                                    L&D/training taken for the last five (5) years for Division
                                    Chief/Executive/Managerial positions)</h6>
                                <hr class="inputting-0">
                                <br class="inputting-0">

                                <div class="form-group row">
                                    <label for="" class="col-sm-1 col-form-label inputting-0">30.</label>
                                    <label for="" class="col-sm-3 col-form-label border-end inputting-0 text-center">
                                        TITLE OF LEARNING AND DEVELOPMENT INTERVENTIONS/TRAINING PROGRAMS <br> (Write in
                                        full)
                                    </label>
                                    <label for="" class="col-sm-3 col-form-label inputting-0 border-end text-center">
                                        INCLUSIVE DATES OF ATTENDANCE <br> (mm/dd/yyyy)
                                        <hr>
                                        <div class="form-group row">
                                            <label for=""
                                                class="col-sm-6 col-form-label border-end inputting-0 text-center">From</label>
                                            <label for=""
                                                class="col-sm-6 col-form-label inputting-0 text-center">To</label>
                                        </div>
                                    </label>
                                    <label for="" class="col-sm-1 col-form-label border-end inputting-0 text-center">
                                        NUMBER OF HOURS
                                    </label>
                                    <label for="" class="col-sm-1 col-form-label border-end inputting-0 text-center">
                                        Type of LD ( Managerial / Supervisory / Technical/etc)
                                    </label>
                                    <label for="" class="col-sm-3 col-form-label border-end inputting-0 text-center">
                                        POSITION / NATURE OF WORK
                                    </label>
                                </div>

                                <br>

                                <div class="form-group row border-top">
                                    <div class="col-sm-4">
                                        <br>
                                        <label class="inputting" for="">TITLE OF LEARNING AND DEVELOPMENT
                                            INTERVENTIONS/TRAINING PROGRAMS <br> (Write in full)</label>
                                        <input type="text" class="form-control" id="TOLADI_TP" placeholder="TOLADI_TP">
                                    </div>


                                    <div class="col-sm-3">
                                        <br>
                                        <div class="form-group row">
                                            <div class="col-sm-6">
                                                <label class="inputting" for="">INCLUSIVE DATES <br> (mm/dd/yyyy)
                                                    From</label>
                                                <input type="text" class="form-control" id="VII_I_D_From"
                                                    placeholder="VII_I_D_From">
                                            </div>
                                            <div class="col-sm-6">
                                                <label class="inputting" for="">INCLUSIVE DATES <br> (mm/dd/yyyy)
                                                    To</label>
                                                <input type="text" class="form-control" id="VII_I_D_To"
                                                    placeholder="VII_I_D_To">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm-1">
                                        <br>
                                        <label class="inputting" for="">NUMBER OF HOURS</label>
                                        <input type="text" class="form-control" id="VII_NOF" placeholder="VII_NOF">
                                    </div>

                                    <div class="col-sm-1">
                                        <br>
                                        <label class="inputting" for="">Type of LD ( Managerial / Supervisory /
                                            Technical/etc) </label>
                                        <input type="text" class="form-control" id="VII_TOLD" placeholder="VII_TOLD">
                                    </div>

                                    <div class="col-sm-3">
                                        <br>
                                        <label class="inputting" for="">POSITION / NATURE OF WORK</label>
                                        <input type="text" class="form-control" id="VII_POS_NOF"
                                            placeholder="VII_POS_NOF">
                                    </div>
                                </div>

                                <br>

                                <div class="form-group row">
                                    <div class="col-sm-2">
                                        <input type="button" class="btn btn-dark" id="LADAdd" value="ADD">
                                    </div>

                                    <br>
                                    <br>
                                    <br>

                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th scope="col">TITLE OF LEARNING AND DEVELOPMENT
                                                        INTERVENTIONS/TRAINING PROGRAMS</th>
                                                    <th scope="col">INCLUSIVE DATES OF ATTENDANCE FROM</th>
                                                    <th scope="col">INCLUSIVE DATES OF ATTENDANCE TO</th>
                                                    <th scope="col">NUMBER OF HOURS</th>
                                                    <th scope="col">Type of LD</th>
                                                    <th scope="col">POSITION / NATURE OF WORK</th>
                                                    <th scope="col">Control</th>
                                                </tr>
                                            </thead>
                                            <tbody id="LAD">

                                            </tbody>
                                        </table>
                                    </div>


                                    <br>
                                    <br>

                                    <hr style="margin: 0;">
                                    <label for="" class="col-sm-12 col-form-label"
                                        style="font-size: 0.5em; color: red; font-style: italic;">
                                        <center>(Continue on separate sheet if necessary)</center>
                                    </label>
                                    <hr>
                                </div>


                                <br>

                                <div class="form-group row">
                                    <label for="" class="col-sm-1 col-form-label">SIGNATURE</label>
                                    <div class="col-sm-5">
                                        <input type="file" class="form-control" id="C3_Signature"
                                            placeholder="C3_Signature">
                                    </div>

                                    <label for="" class="col-sm-1 col-form-label">DATE</label>
                                    <div class="col-sm-5">
                                        <input type="date" class="form-control" id="C3_Date" placeholder="C3_Date">
                                    </div>
                                </div>

                    </div>
                </div>
            </div>
            <!-- END WHOLE C3 -->


            <!-- START WHOLE C4 -->
            <div class="tab-pane fade" id="nav-contact2" role="tabpanel" aria-labelledby="nav-contact2-tab">


                <div class="c4-container">
                    <div class="c4-container-header"></div>
                    <div class="c4-container-body">
                        <br>

                        <div class="form-group row">
                            <label for="" class="col-sm-1 col-form-label">34.</label>
                            <label for="" class="col-sm-6 col-form-label">Are you related by consanguinity or affinity
                                to the appointing or recommending authority, or to the <br>
                                Bureau or Department where you will be apppointed,
                            </label>
                            <label for="" class="col-sm-5 col-form-label"></label>
                        </div>

                        <div class="form-group row">
                            <label for="" class="col-sm-1 col-form-label"></label>
                            <label for="" class="col-sm-6 col-form-label">a. within the third degree?
                            </label>
                            <div for="" class="col-sm-5 col-form-label">

                                <input class="form-check-input" type="radio" name="TD" id="TDYes" value="Yes" checked>
                                <label class="form-check-label" for="TDYes">
                                    Yes
                                </label>

                                &nbsp;&nbsp;&nbsp;&nbsp;

                                <input class="form-check-input" type="radio" name="TD" id="TDNo" value="No">
                                <label class="form-check-label" for="TDNo">
                                    No
                                </label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="" class="col-sm-1 col-form-label"></label>
                            <label for="" class="col-sm-6 col-form-label">b. within the fourth degree (for Local
                                Government Unit - Career Employees)?
                            </label>
                            <div for="" class="col-sm-5 col-form-label">

                                <input class="form-check-input" type="radio" name="FD" id="FDYes" value="Yes" checked>
                                <label class="form-check-label" for="FDYes">
                                    Yes
                                </label>

                                &nbsp;&nbsp;&nbsp;&nbsp;

                                <input class="form-check-input" type="radio" name="FD" id="FDNo" value="No">
                                <label class="form-check-label" for="FDNo">
                                    No
                                </label>

                                <br>
                                <br>
                                <label for="FDDetails">If YES, give details:</label>
                                <textarea class="form-control" id="FDDetails" rows="3"></textarea>
                            </div>
                        </div>

                        <hr>


                        <div class="form-group row">
                            <label for="" class="col-sm-1 col-form-label">35.</label>
                            <label for="" class="col-sm-6 col-form-label">a. Have you ever been found guilty of any
                                administrative offense?
                            </label>
                            <div for="" class="col-sm-5 col-form-label">
                                <input class="form-check-input" type="radio" name="AO" id="AOYes" value="Yes" checked>
                                <label class="form-check-label" for="AOYes">
                                    Yes
                                </label>

                                &nbsp;&nbsp;&nbsp;&nbsp;

                                <input class="form-check-input" type="radio" name="AO" id="AONo" value="No">
                                <label class="form-check-label" for="AONo">
                                    No
                                </label>

                                <br>
                                <br>
                                <label for="AODetails">If YES, give details:</label>
                                <textarea class="form-control" id="AODetails" rows="3"></textarea>
                            </div>
                        </div>

                        <br>

                        <div class="form-group row">
                            <label for="" class="col-sm-1 col-form-label"></label>
                            <label for="" class="col-sm-6 col-form-label">b. Have you been criminally charged before any
                                court?
                            </label>
                            <div for="" class="col-sm-5 col-form-label">
                                <input class="form-check-input" type="radio" name="CC" id="CCYes" value="Yes" checked>
                                <label class="form-check-label" for="CCYes">
                                    Yes
                                </label>

                                &nbsp;&nbsp;&nbsp;&nbsp;

                                <input class="form-check-input" type="radio" name="CC" id="CCNo" value="No">
                                <label class="form-check-label" for="CCNo">
                                    No
                                </label>

                                <br>
                                <br>
                                <label for="CCDate">If YES, give details:</label>
                                <div class="form-group row">
                                    <div class="col-sm-1"></div>
                                    <div class="col-sm-3 col-form-label">
                                        Date Filed:
                                    </div>
                                    <div class="col-sm-8 col-form-label">
                                        <input type="date" class="form-control" id="CCDate" placeholder="CCDate">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-1"></div>
                                    <div class="col-sm-3 col-form-label">
                                        Status of Case/s:
                                    </div>
                                    <div class="col-sm-8 col-form-label">
                                        <input type="text" class="form-control" id="CCSOC" placeholder="CCSOC">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group row">
                            <label for="" class="col-sm-1 col-form-label">36.</label>
                            <label for="" class="col-sm-6 col-form-label">Have you ever been convicted of any crime or
                                violation of any law, decree, ordinance or regulation by any court or tribunal?
                            </label>
                            <div for="" class="col-sm-5 col-form-label">
                                <input class="form-check-input" type="radio" name="CoV" id="CoVYes" value="Yes" checked>
                                <label class="form-check-label" for="CoVYes">
                                    Yes
                                </label>

                                &nbsp;&nbsp;&nbsp;&nbsp;

                                <input class="form-check-input" type="radio" name="CoV" id="CoVNo" value="No">
                                <label class="form-check-label" for="CoVNo">
                                    No
                                </label>

                                <br>
                                <br>
                                <label for="CoVDetails">If YES, give details:</label>
                                <textarea class="form-control" id="CoVDetails" rows="3"></textarea>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group row">
                            <label for="" class="col-sm-1 col-form-label">37.</label>
                            <label for="" class="col-sm-6 col-form-label">
                                Have you ever been separated from the service in any of the following modes:
                                resignation, retirement, dropped from the rolls, dismissal,
                                termination, end of term, finished contract or phased out (abolition) in the public or
                                private sector?

                            </label>
                            <div for="" class="col-sm-5 col-form-label">
                                <input class="form-check-input" type="radio" name="SFTS" id="SFTSYes" value="Yes"
                                    checked>
                                <label class="form-check-label" for="SFTSYes">
                                    Yes
                                </label>

                                &nbsp;&nbsp;&nbsp;&nbsp;

                                <input class="form-check-input" type="radio" name="SFTS" id="SFTSNo" value="No">
                                <label class="form-check-label" for="SFTSNo">
                                    No
                                </label>

                                <br>
                                <br>
                                <label for="SFTSDetails">If YES, give details:</label>
                                <textarea class="form-control" id="SFTSDetails" rows="3"></textarea>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group row">
                            <label for="" class="col-sm-1 col-form-label">38.</label>
                            <label for="" class="col-sm-6 col-form-label">a. Have you ever been a candidate in a
                                national or local election held within the last year (except Barangay election)?
                            </label>
                            <div for="" class="col-sm-5 col-form-label">
                                <input class="form-check-input" type="radio" name="CNOLE" id="CNOLEYes" value="Yes"
                                    checked>
                                <label class="form-check-label" for="CNOLEYes">
                                    Yes
                                </label>

                                &nbsp;&nbsp;&nbsp;&nbsp;

                                <input class="form-check-input" type="radio" name="CNOLE" id="CNOLENo" value="No">
                                <label class="form-check-label" for="CNOLENo">
                                    No
                                </label>

                                <br>
                                <br>
                                <label for="CNOLEDetails">If YES, give details:</label>
                                <textarea class="form-control" id="CNOLEDetails" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="" class="col-sm-1 col-form-label"></label>
                            <label for="" class="col-sm-6 col-form-label">b. Have you resigned from the government
                                service during the three (3)-month period before the last election to promote/actively
                                campaign for a national or local candidate?
                            </label>
                            <div for="" class="col-sm-5 col-form-label">
                                <input class="form-check-input" type="radio" name="RGS" id="RGSYes" value="Yes" checked>
                                <label class="form-check-label" for="RGSYes">
                                    Yes
                                </label>

                                &nbsp;&nbsp;&nbsp;&nbsp;

                                <input class="form-check-input" type="radio" name="RGS" id="RGSNo" value="No">
                                <label class="form-check-label" for="RGSNo">
                                    No
                                </label>

                                <br>
                                <br>
                                <label for="RGSDetails">If YES, give details:</label>
                                <textarea class="form-control" id="RGSDetails" rows="3"></textarea>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group row">
                            <label for="" class="col-sm-1 col-form-label">39.</label>
                            <label for="" class="col-sm-6 col-form-label">Have you acquired the status of an immigrant
                                or permanent resident of another country?
                            </label>
                            <div for="" class="col-sm-5 col-form-label">
                                <input class="form-check-input" type="radio" name="SIoPS" id="SIoPSYes" value="Yes"
                                    checked>
                                <label class="form-check-label" for="SIoPSYes">
                                    Yes
                                </label>

                                &nbsp;&nbsp;&nbsp;&nbsp;

                                <input class="form-check-input" type="radio" name="SIoPS" id="SIoPSNo" value="No">
                                <label class="form-check-label" for="SIoPSNo">
                                    No
                                </label>

                                <br>
                                <br>
                                <label for="SIoPSDetails">If YES, give details:</label>
                                <textarea class="form-control" id="SIoPSDetails" rows="3"></textarea>
                            </div>
                        </div>

                        <hr>


                        <div class="form-group row">
                            <label for="" class="col-sm-1 col-form-label">40.</label>
                            <label for="" class="col-sm-6 col-form-label">Pursuant to: (a) Indigenous People's Act (RA
                                8371); (b) Magna Carta for Disabled Persons (RA 7277); and (c) Solo Parents Welfare Act
                                of 2000 (RA 8972), please answer the following items:
                            </label>
                            <label for="" class="col-sm-5 col-form-label"></label>
                        </div>

                        <div class="form-group row">
                            <label for="" class="col-sm-1 col-form-label">a.</label>
                            <label for="" class="col-sm-6 col-form-label">Are you a member of any indigenous group?
                            </label>
                            <div for="" class="col-sm-5 col-form-label">
                                <input class="form-check-input" type="radio" name="IG" id="IGYes" value="Yes" checked>
                                <label class="form-check-label" for="IGYes">
                                    Yes
                                </label>

                                &nbsp;&nbsp;&nbsp;&nbsp;

                                <input class="form-check-input" type="radio" name="IG" id="IGNo" value="No">
                                <label class="form-check-label" for="IGNo">
                                    No
                                </label>

                                <br>
                                <br>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        If Yes, please specify:
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="IGDetails" placeholder="IGDetails">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="" class="col-sm-1 col-form-label">b.</label>
                            <label for="" class="col-sm-6 col-form-label">Are you a person with disability?
                            </label>
                            <div for="" class="col-sm-5 col-form-label">
                                <input class="form-check-input" type="radio" name="PwD" id="PwDYes" value="Yes" checked>
                                <label class="form-check-label" for="PwDYes">
                                    Yes
                                </label>

                                &nbsp;&nbsp;&nbsp;&nbsp;

                                <input class="form-check-input" type="radio" name="PwD" id="PwDNo" value="No">
                                <label class="form-check-label" for="PwDNo">
                                    No
                                </label>

                                <br>
                                <br>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        If Yes, please specify ID No:
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="PwDDetails"
                                            placeholder="PwDDetails">
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="form-group row">
                            <label for="" class="col-sm-1 col-form-label">c.</label>
                            <label for="" class="col-sm-6 col-form-label">Are you a solo parent?
                            </label>
                            <div for="" class="col-sm-5 col-form-label">
                                <input class="form-check-input" type="radio" name="SP" id="SPYes" value="Yes" checked>
                                <label class="form-check-label" for="SPYes">
                                    Yes
                                </label>

                                &nbsp;&nbsp;&nbsp;&nbsp;

                                <input class="form-check-input" type="radio" name="SP" id="SPNo" value="No">
                                <label class="form-check-label" for="SPNo">
                                    No
                                </label>

                                <br>
                                <br>
                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        If Yes, please specify ID No:
                                    </div>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="SPDetails" placeholder="SPDetails">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="holder-box-3">
                            <div class="box-2">

                                <div class="form-group row">
                                    <label for="" class="col-sm-1">41.</label>
                                    <label for="" class="col-sm-11">REFERENCES (Person not related by consanguinity or
                                        affinity to applicant /appointee)</label>
                                </div>

                                <br>

                                <div class="form-group row">
                                    <label for="" class="col-sm-4 col-form-labe border-end inputting-0 text-center">
                                        NAME
                                    </label>
                                    <label for="" class="col-sm-4 col-form-label border-end inputting-0 text-center">
                                        ADDRESS
                                    </label>
                                    <label for="" class="col-sm-4 col-form-label inputting-0 text-center">
                                        TEL. NO.
                                    </label>

                                </div>



                                <div class="form-group row">

                                    <div class="col-sm-4">
                                        <br>
                                        <label class="inputting" for="RefName">NAME</label>
                                        <input type="text" class="form-control" id="RefName" placeholder="RefName">
                                    </div>

                                    <div class="col-sm-4">
                                        <br>
                                        <label class="inputting" for="">ADDRESS</label>
                                        <input type="text" class="form-control" id="RefAddress"
                                            placeholder="RefAddress">
                                    </div>

                                    <div class="col-sm-4">
                                        <br>
                                        <label class="inputting" for="">TEL. NO.</label>
                                        <input type="text" class="form-control" id="RefTelNo" placeholder="RefTelNo">
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-sm-4">
                                            <br>
                                            <input type="button" class="btn btn-dark" id="ReferencesAdd" value="ADD">
                                            <br>
                                            <br>
                                        </div>
                                    </div>

                                    <br>

                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">NAME</th>
                                                            <th scope="col">ADDRESS</th>
                                                            <th scope="col">TEL. NO.</th>
                                                            <th scope="col">Control</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="reference">

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <hr>

                                <div class="form-group row">
                                    <label for="" class="col-sm-1">42.</label>
                                    <label for="" class="col-sm-11">
                                        I declare under oath that I have personally accomplished this Personal Data
                                        Sheet which is a true, correct and complete statement pursuant to the provisions
                                        of pertinent laws, rules and regulations of the Republic of the Philippines. I
                                        authorize the agency head/authorized representative to verify/validate the
                                        contents stated herein. I agree that any misrepresentation made in this document
                                        and its attachments shall cause the filing of administrative/criminal case/s
                                        against me.

                                    </label>
                                </div>

                                <hr>

                            </div>
                            <div class="box-3">
                                <div class="box-3-picturebox">
                                    <div class="box-3-picturebox-solo">

                                        ID picture taken within
                                        the last 6 months
                                        3.5 cm. X 4.5 cm
                                        (passport size)

                                        With full and handwritten
                                        name tag and signature over
                                        printed name

                                        Computer generated
                                        or photocopied picture
                                        is not acceptable

                                        <br>
                                        <br>

                                        <input type="file" class="form-control" id="C4_Picture" placeholder="C4_Picture"
                                            name="C4_Picture">

                                    </div>

                                    <div class="box-3-picturebox-caption">
                                        PHOTO
                                    </div>
                                </div>
                            </div>

                        </div>

                        <hr>

                        <div class="form-group row">
                            <div class="col-sm-4 border" style="padding: 15px;">

                                <div class="form-group row">
                                    <div class="col-sm-12">Government Issued ID (i.e.Passport, GSIS, SSS, PRC, Driver's
                                        License, etc.) <br> PLEASE INDICATE ID Number and Date of Issuance</div>
                                </div>

                                <br>

                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <div class="form-group row">
                                            <div class="col-sm-5">
                                                Government Issued ID:
                                            </div>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control" id="GIID" placeholder="GIID">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <br>

                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <div class="form-group row">
                                            <div class="col-sm-5">
                                                ID/License/Passport No.:
                                            </div>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control" id="ID_L_PNO"
                                                    placeholder="ID_L_PNO">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <br>

                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <div class="form-group row">
                                            <div class="col-sm-5">
                                                Date/Place of Issuance:
                                            </div>
                                            <div class="col-sm-7">
                                                <input type="text" class="form-control" id="D_PoI" placeholder="D_PoI">
                                            </div>
                                        </div>
                                    </div>
                                </div>





                            </div>

                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                            <div class="col-sm-4 border" style="padding: 15px;">
                                <br>

                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <input type="file" class="form-control" id="C4_Signature"
                                            placeholder="C4_Signature" name="C4_Signature">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-12 text-center">
                                        Signature (Sign inside the box)
                                    </div>
                                </div>

                                <br>

                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <input type="date" class="form-control" id="C4_Date" placeholder="C4_Date"
                                            name="C4_Date">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-12 text-center">
                                        Date Accomplished
                                    </div>
                                </div>

                            </div>

                            <br>

                            <div class="col-sm-3 submit-button" style="position: relative;">
                                <input type="hidden" name="info" id="info">
                                <input type="button" id="Submit" class="btn btn-primary"
                                    style="bottom: 0; right: 0; position: absolute;" value="Submit">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END WHOLE C4 -->

        </div>

        <br>
    </div>

</body>

</html>