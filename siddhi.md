<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'auth_check.php';
require_once 'Constant.php';
require_once 'db.php';

$db = new Database();
$csvFile = Constant::CSVFILE;
$csvCharFile = Constant::CSVCHARFILE;
$data = [];
$chardata = [];
$defaultProfileImage = 'images/profile.png';
$defaltSignature = 'images/signature.png';
//csv data
if (isset($_GET['type']) && $_GET['ajax'] === '1') {
    $type = $_GET['type'];

    if ($type === 'standard') {
        $response = loadCsvData(Constant::CSVFILE);
    } elseif ($type === 'character') {
        $response = loadCsvData(Constant::CSVCHARFILE);
    } elseif ($type === 'random') {
        $response = loadCsvData(Constant::CSVRANDOMFILE);
    } else {
        $response = [];
    }

    //Generate and return HTML rows
    $i = 0;
    foreach ($response as $key => $value): ?>
        <tr class="data-row">
            <td><input type="text" name="field[<?= $i ?>][name]" class="form-control" value="<?= htmlspecialchars($key) ?>" readonly></td>
            <td><input type="text" name="field[<?= $i ?>][value]" class="form-control" value="<?= htmlspecialchars($value) ?>" readonly></td>
            <td>
                <button type="button" class="btn btn-danger deleteBtn" data-key="<?= htmlspecialchars($key) ?>"><i class="fa-solid fa-trash"></i></button>
            </td>
        </tr>
    <?php
        $i++;
    endforeach;

    exit;
}

//pdf
$pdfList = [];
$pdfList = $db->getAllRecords();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>SVG to PDF Converter</title>
        <link href="assets/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="assets/css/font.css">
        <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" /> -->
        <link rel="stylesheet" href="assets/css/all.min.css" />
        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/jquery-ui.js"></script>
        <script src="assets/js/bootstrap.bundle.min.js"></script>
        <script src="assets/js/fileUpload.js"></script>
        <link rel="stylesheet" href="assets/css/dataTables.bootstrap5.min.css">
        <!-- DataTables JS -->
        <script src="assets/js/jquery.dataTables.min.js"></script>
        <script src="assets/js/dataTables.bootstrap5.min.js"></script>

    </head>
    <body>
        <div id="overlay" class="overlay">
            <div id="loader-icon"></div>
        </div>
        <div class="container mt-5 oocard-svg-to-pdf">
            <h1 class="mb-4">OOcard |  SVG->PDF</h1>
            <!-- <form id="uploadForm" action="convertbg.php" method="post" enctype="multipart/form-data"> -->
            <!-- Navigation Tabs -->
            <div class="section-tabs d-flex flex-wrap justify-content-between gap-2">
                <a href="#backgrounds" class="section-tab"><i class="fas fa-image"></i> Backgrounds</a>
                <a href="#svg-templates" class="section-tab"><i class="fas fa-vector-square"></i> SVG Templates</a>
                <a href="#images-signatures" class="section-tab"><i class="fas fa-pen-nib"></i> Images & Signatures</a>
                <a href="#test-data" class="section-tab"><i class="fas fa-database"></i> Test Data</a>
                <a href="#fonts" class="section-tab"><i class="fas fa-font"></i> Fonts</a>
            </div>
            <!-- Sections -->
             <section id="backgrounds">
                <h3 class="section-title mb-4">Backgrounds</h3>
                <div class="row">
                    <!-- Left Side: Background Selection -->
                    <div class="col-12 col-lg-5 mb-4">
                        <form id="uploadBGForm" action="ajax.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="uploadFiles" />
                            <div class="mb-4">
                            <label class="form-label">Upload Background</label>
                            <div class="d-flex flex-column">
                                <div id="fileUpload" data-accept=".png" class="file-container"></div>
                                <div id="messagebox"></div>
                                <button type="submit" class="btn btn-primary mt-3" id="btnUploadImages">Click to Upload</button>
                            </div>
                            </div>
                        </form>
                    </div>

                    <!-- Right Side: Upload New Backgrounds -->
                    <div class="col-12 col-lg-7">
                    <div class="mb-3">
                        <button type="button" id="faceBtn" class="tab-btn active">Face</button>
                        <button type="button" id="reverseBtn" class="tab-btn">Reverse</button>
                    </div>

                    <form id="uploadForm" action="convertbg.php" method="post" enctype="multipart/form-data">
                        <div id="section_bgimage">
                            <div class="back-face-reverse-checkbox">
                                <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="checkall">
                                <label class="form-check-label" for="checkall">Select All</label>
                                </div>
                                <div class="form-check mb-2 back-reverse-checkbox">
                                <input type="checkbox" class="form-check-input" id="sameBothSides">
                                <label class="form-check-label" for="sameBothSides">Same backgrounds both sides</label>
                                </div>
                            </div>

                            <!-- Face Image Selection -->
                            <div id="faceImageBox" class="bgimage-box d-flex flex-wrap mt-3">
                                <?php
                                $i = 0;
                                foreach (scandir(Constant::PNG_FOLDER) as $file):
                                if ($file != '.' && $file != '..'):
                                ?>
                                <div class="image-container p-1 col-4 col-sm-3 col-md-2 col-lg-2">
                                    <input class="bgimagescheckbox face" type="checkbox" id="face_file_<?= $i ?>" name="bgimageFace[]" value="<?= $file ?>" />
                                    <label for="face_file_<?= $i ?>" class="image-label position-relative d-block">
                                    <img src="<?= Constant::PNG_FOLDER . '/' . $file ?>" class="selectedImages w-100" />
                                    <span class="delete-wrapper">
                                        <button type="button" class="btn btn-danger deleteBtn" data-file="<?= $file ?>">
                                        <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </span>
                                    </label>
                                </div>
                                <?php $i++; endif; endforeach; ?>
                            </div>

                            <!-- Reverse Image Selection -->
                            <div id="reverseImageBox" class="bgimage-box d-none d-flex flex-wrap mt-3">
                                <?php
                                $i = 0;
                                foreach (scandir(Constant::PNG_FOLDER) as $file):
                                if ($file != '.' && $file != '..'):
                                ?>
                                <div class="image-container p-1 col-4 col-sm-3 col-md-2 col-lg-2">
                                    <input class="bgimagescheckbox reverse" type="checkbox" id="reverse_file_<?= $i ?>" name="bgimageReverse[]" value="<?= $file ?>" />
                                    <label for="reverse_file_<?= $i ?>" class="image-label position-relative d-block">
                                    <img src="<?= Constant::PNG_FOLDER . '/' . $file ?>" class="selectedImages w-100" />
                                    <span class="delete-wrapper">
                                        <button type="button" class="btn btn-danger deleteBtn" data-file="<?= $file ?>">
                                        <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </span>
                                    </label>
                                </div>
                                <?php $i++; endif; endforeach; ?>
                            </div>
                        </div>
                        <input type="hidden" name="action" value="convertbg" />
                        <input type="hidden" name="sid" value="<?= time() ?>" />
                        <input type="hidden" id="hdnfrontSvgChecked" name="frontSvgChecked" value="1" />
                        <input type="hidden" id="hdnreverseSvgChecked" name="reverseSvgChecked" value="1" />
                        <input type="hidden" id="hdnsvgfile" name="svgfile" value="<?= Constant::MAIN_SVGFILE_PATH ?>" />
                        <input type="hidden" id="hdnreversesvgfile" name="reversesvgfile" value="<?= Constant::REVERSE_SVGFILE_PATH ?>" />
                        <input type="hidden" id="hdnfilename_option" name="filename_option" value="datetime_seq" />
                        <input type="hidden" id="hdncustom_prefix" name="custom_prefix" value="" />
                        <input type="hidden" id="convertToPDF" name="convertTo" value="pdf" />
                        <input type="hidden" id="hdnprofile" name="profile" value="<?= $defaultProfileImage ?>" />
                        <input type="hidden" id="hdnsignature" name="signature" value="<?= $defaltSignature ?>" />
                    </form>
                    </div>
                </div>
            </section>


            <!-- front reverse svg -->
            <section id="svg-templates" class="mb-5">
                <h3 class="section-title mb-4">SVG Templates</h3>
                <div class="row">

                    <!-- Left: SVG Previews + Upload -->
                    <div class="col-12 col-lg-7">
                        <div class="row">
                            <!-- SVG Previews -->
                            <!-- Output Filename Options -->
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="filename_option" value="datetime_seq" id="datetime_seq" checked="checked">
                                <label class="form-check-label" for="datetime_seq">Date & Time + Sequential Number</label>
                            </div>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="filename_option" value="user_datetime_seq" id="user_datetime_seq">
                                <label class="form-check-label" for="user_datetime_seq">User + Date & Time + Sequential Number</label>
                            </div>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="filename_option" value="custom_datetime_seq" id="custom_datetime_seq">
                                <label class="form-check-label" for="custom_datetime_seq">Custom + Date & Time + Sequential Number</label>
                            </div>

                            <div id="customTextBox" class="mt-2 d-none">
                                <input type="text" id="custom_prefix" name="custom_prefix" class="form-control" placeholder="Enter custom prefix">
                            </div>

                            <!-- <div class="col-6 d-flex flex-column align-items-center mb-3 mt-3">
                                <label class="form-check-label mb-2">
                                    <input type="checkbox" name="frontSvgChecked" checked value="1" /> Front SVG
                                </label>
                                <div class="bg-light border rounded d-flex align-items-center justify-content-center mb-3" style="height: 150px; width: 100%;">
                                    <img src="<?= Constant::MAIN_SVGFILE_PATH ?>" id="mainSvg" class="img-fluid" style="max-height: 100%; max-width: 100%;" />
                                </div>
                                <div class="col-4">
                                    <input type="file" id="fileInput" name="svgfile" accept=".svg" class="d-none" onchange="previewSVGImage('mainSvg')" />
                                    <button type="button" class="btn btn-primary w-full" onclick="document.getElementById('fileInput').click()">Upload New</button>
                                </div>
                            </div> -->
                            <div class="col-12 col-md-6 d-flex flex-column align-items-center mb-3 mt-3">
                                <label class="form-check-label mb-2">
                                    <input type="checkbox" name="frontSvgChecked" checked value="1" /> Front SVG
                                </label>
                                <div class="w-100 d-flex justify-content-center">
                                    <div class="bg-light border rounded d-flex align-items-center justify-content-center mb-3" style="height: 150px; width: 100%; max-width: 400px;">
                                        <img src="<?= Constant::MAIN_SVGFILE_PATH ?>" id="mainSvg" class="img-fluid" style="max-height: 100%; max-width: 100%;" />
                                    </div>
                                </div>
                                <div class="col-12 d-flex justify-content-center">
                                    <div class="col-8 col-md-6">
                                        <input type="file" id="fileInput" name="svgfile" accept=".svg" class="d-none" onchange="previewSVGImage('mainSvg')" />
                                        <button type="button" class="btn btn-primary w-100" onclick="document.getElementById('fileInput').click()">Upload New</button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6 d-flex flex-column align-items-center mb-3 mt-3">
                                <label class="form-check-label mb-2">
                                    <input type="checkbox" name="reverseSvgChecked" checked value="1" /> Reverse SVG
                                </label>
                                <div class="w-100 d-flex justify-content-center">
                                    <div class="bg-light border rounded d-flex align-items-center justify-content-center mb-3" style="height: 150px; width: 100%;  max-width: 400px;">
                                        <img src="<?= Constant::REVERSE_SVGFILE_PATH ?>" id="mainReverseSvg" class="img-fluid" style="max-height: 100%; max-width: 100%;" />
                                    </div>
                                </div>
                                <div class="col-12 d-flex justify-content-center">
                                    <div class="col-8 col-md-6">
                                        <input type="file" id="fileInputReverse" name="reversesvgfile" accept=".svg" class="d-none" onchange="previewSVGImage('mainReverseSvg')" />
                                        <button type="button" class="btn btn-primary w-100" onclick="document.getElementById('fileInputReverse').click()">Upload New</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Test Results -->
                    <div class="col-12 col-lg-5 mt-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-center mb-3" style="text-align: center;">
                                <div class="col-lg-6 col-md-6 col-12">
                                    <button type="button" class="btn btn-secondary w-75" id="runSvgTestBtn">Test SVG</button>
                                </div>
                            </div>
                            <div id="testResultContainer" class="mt-4"></div>
                        </div>
                    </div>
            </section>

            <!-- profilephoto & signature -->
            <section id="images-signatures">
                <h3 class="section-title mb-4">Images & Signatures</h3>            
                <div class="row">
                    <!-- Profile Images -->
                    <div id="section_profile" class="col-lg-6 col-md-6 col-12 d-flex flex-column align-items-center">
                        <h4 class="col-12 d-flex flex-column align-items-left">Images</h4>
                        <div class="slider-wrapper position-relative w-100">
                            <div class="slider-container">
                                <button type="button" class="slider-arrow left" onclick="scrollSlider('left', 'image-slider')">‹</button>
                                <div class="slider" id="image-slider">
                                    <?php
                                    $i = 0;
                                    foreach (scandir(Constant::PROFILE_FOLDER) as $file):
                                        if ($file != '.' && $file != '..'): ?>
                                        <div class="image-item text-center image-container">
                                            <input type="radio" class="d-none bgimagesradio" id="profile_id_<?= $i ?>" name="profile" value="<?= $file ?>" <?= $i === 0 ? 'checked' : '' ?>/>
                                            <label for="profile_id_<?= $i ?>" class="image-label position-relative d-inline-block">
                                                <div class="img-wrapper">
                                                    <img src="<?= Constant::PROFILE_FOLDER . '/' . $file ?>" class="img-fluid bgradioscheckbox" />
                                                </div>
                                                <span class="delete-wrapper">
                                                    <button type="button" class="btn btn-danger deleteBtn" data-file="<?= $file ?>">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </span>
                                            </label>
                                        </div>
                                        <?php $i++; endif;
                                    endforeach; ?>
                                </div>
                                <button type="button" class="slider-arrow right" onclick="scrollSlider('right', 'image-slider')">›</button>
                            </div>
                        </div>
                        <div class="text-end mt-2 w-100 px-3">
                            <input type="file" id="imageInput" name="image" accept="image/*" class="d-none"/>
                            <button type="button" class="btn btn-primary w-40" onclick="document.getElementById('imageInput').click()">Upload New</button>
                        </div>
                    </div>

                    <!-- Signatures -->
                    <div id="section_sign" class="col-lg-6 col-md-6 col-12 d-flex flex-column align-items-center">
                        <h4 class="col-12 d-flex flex-column align-items-left">Signatures</h4>
                        <div class="slider-wrapper position-relative w-100">
                            <div class="slider-container">
                                <button type="button" class="slider-arrow left" onclick="scrollSlider('left', 'signature-slider')">‹</button>
                                <div class="slider" id="signature-slider">
                                    <?php
                                    $i = 0;
                                    foreach (scandir(Constant::SIGNATURE_FOLDER) as $file):
                                        if ($file != '.' && $file != '..'): ?>
                                        <div class="signature-item text-center image-container">
                                            <input type="radio" class="d-none bgsignradio" id="signature_id_<?= $i ?>" name="signature" value="<?= $file ?>" <?= $i === 0 ? 'checked' : '' ?>/>
                                            <label for="signature_id_<?= $i ?>" class="image-label position-relative d-inline-block">
                                                <img src="<?= Constant::SIGNATURE_FOLDER . '/' . $file ?>" class="img-fluid bgsigncheckbox" />
                                                <span class="delete-wrapper">
                                                    <button type="button" class="btn btn-danger deleteBtn" data-file="<?= $file ?>">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </span>
                                            </label>
                                        </div>
                                        <?php $i++; endif;
                                    endforeach; ?>
                                </div>
                                <button type="button" class="slider-arrow right" onclick="scrollSlider('right', 'signature-slider')">›</button>
                            </div>
                        </div>
                        <div class="text-end mt-2 w-100 px-3">
                            <input type="file" id="signatureInput" name="signature" accept="image/*" class="d-none"/>
                            <button type="button" class="btn btn-primary w-40" onclick="document.getElementById('signatureInput').click()">Upload New</button>
                        </div>
                    </div>
                </div>
                <!-- pdf png converter -->
                <div class="col-md-12 mb-4">
                        <div class="ConvertTab" style="display: none;">
                            <span>Convert to:</span>
                            <label for="convertToPDF" class="convertpdf">PDF</label>
                            <input type="radio" name="convertTo" checked="checked" id="convertToPDF" value="pdf" checked="checked" class="convertpdf" />
                        </div>
                    <button type="submit" class="btn btn-primary mt-4" id="submitBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Convert
                    </button>
                </div>
                <div id="success-message-info"></div>
                <!-- <div id="uploadMessage" class="mt-2"></div> -->
            </section>
        <!-- </form> -->
            <div id="errorMessage" class="alert alert-danger mt-3 d-none"></div>

            <!-- show additional fields -->
            <section id="test-data">
                <h3 class="section-title mb-4">Test Data</h3>
                <!-- Test Data Options -->
                <div class="test-data-radio">
                    <div class="form-check">
                        <input class="form-check-input test-group" type="radio" name="testData" id="standardData" checked>
                        <label class="form-check-label" for="standardData">Use Standard Test Data</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input test-group" type="radio" name="testData" id="characterData">
                        <label class="form-check-label" for="characterData">Use Characters Test Data</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input test-group" type="radio" name="testData" id="randomData">
                        <label class="form-check-label" for="randomData">Use set of random card records from oocard.com</label>
                    </div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="editTestData">
                    <label class="form-check-label" for="editTestData">Edit Test Data</label>
                </div>

                <div id="testDataOutput" class="mt-3" style="white-space: pre-wrap;"></div>
                <div id="section_fields"  class="card" style="margin:15px 0;">
                    <div class="card-body">
                        <button type="button" class="btn btn-secondary mb-3" id="toggleTableBtn">Show All Fields</button>
                        <form id="infoForm" method="post" action="ajax.php">
                            <input type="hidden" name="action" value="saveData" />
                            <input type="hidden" name="data_type" id="data_type" value="character" />
                            <table id="dataTable" class="table table-bordered align-middle text-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Field Name</th>
                                        <th scope="col">Value</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="3">
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="submit" class="btn btn-primary" id="saveData">Save</button>
                                            <button type="button" class="btn btn-primary" id="addNewBtn" data-inc="<?= $i ?>">Add New</button>
                                            <button type="button" class="btn btn-warning" id="resetBtn">Reset</button>
                                        </div>
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </form>
                    </div>
                </div>
            </section>
            <!-- Fonts & GeratedPdfs -->
            <section id="fonts" class="svg-pdf-fonts">
                <div id="div-fonts">
                    <h3 class="section-title mb-4">Fonts </h3>  
                    <button type="button" class="btn btn-secondary mb-3" id="toggleTableBtnFont">Show All Fonts</button>
                    <?php $fonts = $db->getFonts(); ?>
                    <ol class="list-group list-group-numbered">
                        <?php foreach ($fonts as $font): ?>
                            <li class="list-group-item font-row"><?= htmlspecialchars($font) ?></li>
                        <?php endforeach; ?>
                    </ol>
                    <form id="fontUploadForm" enctype="multipart/form-data" class="mt-3">
                        <input type="file" name="fontfile" id="fontFileInput" accept=".ttf,.otf,.woff,.woff2" hidden />
                        <button type="button" class="btn btn-primary" id="uploadFontBtn">Upload Font</button>
                <div id="uploadMessage" class="mt-2"></div>
                    </form>
                </div>
                <div id="generatepdf">
                    <section id="generated-pdfs" class="generated-pdfs">
                        <h3 class="section-title mb-4">Generated PDFs</h3>

                        <!-- Responsive Wrapper -->
                         <div class="table-responsive">
                            <table id="pdfTable" class="table table-bordered align-middle text-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>File Name</th>
                                        <th>Date</th>
                                        <th>Created By</th>
                                        <th>Pages</th>
                                        <th>Tools</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pdfList as $pdf): 
                                        $fullPdfPath = '/svgtopdf/images/pdf/' . $pdf['pdf_name']; 
                                    ?>
                                        <tr>
                                            <td><?= $pdf['id'] ?></td>
                                            <td><?= htmlspecialchars($pdf['pdf_name']) ?></td>
                                            <td><?= $pdf['created_by'] ?></td>
                                            <td><?= $pdf['username'] ?></td>
                                            <td><?= $pdf['pages'] ?></td>
                                            <td>
                                                <a href="<?= $fullPdfPath ?>" class="btn btn-dark btn-sm me-1" download>
                                                    <i class="fa-solid fa-download"></i>
                                                </a>
                                                <?php
                                                    $filenameWithExt = basename($fullPdfPath);
                                                    $filenameOnly = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                                                    $svgBaseName = preg_replace('/_(Hcards|Scards|Mcards)$/i', '', $filenameOnly);
                                                    $svgZipUrl = '/svgtopdf/images/svgzip/' . $svgBaseName . '.zip';
                                                ?>
                                                <a href="<?= $svgZipUrl ?>" class="btn btn-dark btn-sm me-1" download>
                                                    Download SVG
                                                </a>
                                                <button class="btn btn-danger btn-sm delete-pdf" data-path="<?= $fullPdfPath ?>" data-id="<?= $pdf['id'] ?>">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </section>
        </div>
        <script type="text/javascript">
        var pngPath = "<?= Constant::PNG_FOLDER.'/' ?>";
        function previewSVGImage(elementId){
            var previewBox = document.getElementById(elementId);
            previewBox.src = URL.createObjectURL(event.target.files[0]);
        }
        $(document).ready(function() {
            //hide button
            const $reverseBox = $('#reverseImageBox');
            const $sameBothSidesCheckbox = $('#sameBothSides').closest('.back-reverse-checkbox');

            // Function to check visibility and toggle display
            function toggleSameBothSides() {
                if ($reverseBox.is(':visible')) {
                    $sameBothSidesCheckbox.hide();
                } else {
                    $sameBothSidesCheckbox.show();
                }
            }

            // Run on load
            toggleSameBothSides();

            // Use MutationObserver in jQuery context
            const observer = new MutationObserver(function () {
                toggleSameBothSides();
            });

            observer.observe($reverseBox[0], {
                attributes: true,
                attributeFilter: ['class']
            });

            //data pdftable
            $('#pdfTable').DataTable({
                "pageLength": 5,     
                "lengthChange": false,
                "ordering": true,
                "searching": true,
                "order": [[0, "desc"]],
                "language": {
                    "search": "Filter:"
                }
            });

            //textbox hide on without custom option
            $('input[name="filename_option"]').on('change', function () {
                if ($(this).val() === 'custom_datetime_seq' && $(this).is(':checked')) {
                    $('#customTextBox').removeClass('d-none');
                } else {
                    $('#customTextBox').addClass('d-none');
                }
            });

            $("#faceImageBox").sortable();
            $("#reverseImageBox").sortable();
            $("#fileUpload").fileUpload();
            // submit uploadForm using ajax
            $("#submitBtn").on('click',function(e){
                e.preventDefault(); // prevent immediate submission until values are set

                // Example of setting hidden input values dynamically
                $("#hdnfrontSvgChecked").val($("input[name='frontSvgChecked']").is(":checked") ? 1 : 0);
                $("#hdnreverseSvgChecked").val($("input[name='reverseSvgChecked']").is(":checked") ? 1 : 0);
                
                // Set SVG file paths from image elements or other sources
                $("#hdnsvgfile").val($("#mainSvg").attr("src"));
                $("#hdnreversesvgfile").val($("#mainReverseSvg").attr("src"));

                // Example: file name option from a select dropdown
                $("#hdnfilename_option").val($('input[name="filename_option"]:checked').val());

                // Custom prefix from an input field
                $("#hdncustom_prefix").val($("#custom_prefix").val());

                // Profile image
                $("#hdnprofile").val($('input[name="profile"]:checked').val());

                // Signature image
                $("#hdnsignature").val($('input[name="signature"]:checked').val());

               $("#uploadForm").submit();
            });

            $("#uploadForm").submit(function(e) {
                e.preventDefault();

                const formData = new FormData(this); // ← this grabs all file inputs automatically

                // Add extra fields if needed (like forced boolean checkboxes)
                formData.set("frontSvgChecked", $('input[name="frontSvgChecked"]').is(':checked') ? 1 : 0);
                formData.set("reverseSvgChecked", $('input[name="reverseSvgChecked"]').is(':checked') ? 1 : 0);

                // Validation
                let faceSelected = $("input.bgimagescheckbox.face:checked").length;
                let reverseSelected = $("input.bgimagescheckbox.reverse:checked").length;

                if (faceSelected === 0 && reverseSelected === 0) {
                    alert('Select at least one Face and one Reverse background image.');
                    return false;
                }

                $('#overlay').show();
                $('#success-message-info').html('');

                $.ajax({
                    type: "POST",
                    url: "convertbg.php?sid=" + Math.random(),
                    data: formData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function (data) {
                        $('#overlay').hide();
                        try {
                            // If the response contains multiple JSON objects glued together (e.g. }{)
                            const parts = data.split('}{');
                            const responseStr = parts.length > 1 ? '{' + parts[1] : data;

                            const res = JSON.parse(responseStr);

                            if (res.success) {
                                $('#responseHtml').html(res.html);

                                // Extract the SVG zip link from the embedded HTML
                                const svgMatch = res.html.match(/href="([^"]+\.zip\?sid=\d+)"/);
                                if (svgMatch && svgMatch[1]) {
                                    $('#downloadSvgBtn')
                                        .attr('href', svgMatch[1])
                                        .removeClass('disabled')
                                        .css('pointer-events', 'auto');
                                }

                                // Show success message (optional)
                                $('#success-message-info').html("PDF Generated Successfully. " + res.html);
                            } else {
                                $('#success-message-info').html("Error: " + res.message);
                            }
                        } catch (err) {
                            console.error('Invalid JSON response', err, data);
                            $('#success-message-info').html("Failed to parse response");
                        }
                    }
                });
            });

            // submit uploadBGForm using ajax 
            $("#uploadBGForm").submit(function(e) {
                e.preventDefault();
                $('#overlay').show();
                $('#success-message-info').html('');
                var formData = new FormData(this);
                $.ajax({
                    type: "POST",
                    url: "ajax.php?sid="+Math.random(),
                    data: formData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(data) {
                        const res = JSON.parse(data);
                        $('#overlay').hide();
                        if (res.success === true) {
                            var faceHtml = '';
                            var reverseHtml = '';
                            res.files.forEach(function(file, i) {
                                faceHtml += `
                                    <div class="image-container p-1 col-4 col-sm-3 col-md-2 col-lg-2">
                                        <input class="bgimagescheckbox face" type="checkbox" id="face_file_${i}" name="bgimageFace[]" value="${file}" />
                                        <label for="face_file_${i}" class="image-label position-relative d-block">
                                            <img src="${pngPath + file}" class="selectedImages w-100" />
                                            <span class="delete-wrapper">
                                                <button type="button" class="btn btn-danger deleteBtn" data-file="${file}">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </span>
                                        </label>
                                    </div>
                                `;

                                reverseHtml += `
                                    <div class="image-container p-1 col-4 col-sm-3 col-md-2 col-lg-2">
                                        <input class="bgimagescheckbox reverse" type="checkbox" id="reverse_file_${i}" name="bgimageReverse[]" value="${file}" />
                                        <label for="reverse_file_${i}" class="image-label position-relative d-block">
                                            <img src="${pngPath + file}" class="selectedImages w-100" />
                                            <span class="delete-wrapper">
                                                <button type="button" class="btn btn-danger deleteBtn" data-file="${file}">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </span>
                                        </label>
                                    </div>
                                `;
                            });

                            $('#faceImageBox').html(faceHtml);
                            $('#reverseImageBox').html(reverseHtml);
                        } else {
                            $('#messagebox').html("Error " + res.message);
                        }
                    }
                });
            });

            $(document).on('change','#checkall',function(){
                var checked = $(this).prop('checked');
                if(checked){
                    $('.bgimagescheckbox').prop('checked','checked');
                    $(".selectedImages").addClass('active');
                }else{
                    $('.bgimagescheckbox').prop('checked','');
                    $(".selectedImages").removeClass('active');
                }
            });

            // Handle delete button click
            $(document).on('click', '#section_fields .deleteBtn', function() {
                const key = $(this).data('key');
                const confirmDelete = confirm("Are you sure you want to delete this row?");
                
                if (confirmDelete) {
                    $.ajax({
                        url: 'ajax.php', // This is the same file
                        type: 'POST',
                        data: { deleteKey: key,action:'deleteKey' },
                        success: function(response) {
                            const res = JSON.parse(response);
                            if (res.success) {
                                $(this).closest('tr').remove();
                            } else {
                                alert("Error deleting row: " + res.message);
                            }
                        }.bind(this),
                        error: function() {
                            alert("An error occurred. Please try again.");
                        }
                    });
                }
            });

            $(document).on('click', '#section_bgimage .deleteBtn', function() {
                const file = $(this).data('file');
                const confirmDelete = confirm("Are you sure you want to delete this row?");

                if (confirmDelete) {
                    $.ajax({
                        url: 'ajax.php', // This is the same file
                        type: 'POST',
                        data: { deleteFile: file,action:'deleteBgFile' },
                        success: function(response) {
                            const res = JSON.parse(response);
                            if (res.success) {
                                $(this).closest('div').remove();
                            } else {
                                alert("Error deleting row: " + res.message);
                            }
                        }.bind(this),
                        error: function() {
                            alert("An error occurred. Please try again.");
                        }
                    });
                }
            });

            $(document).on('click', '#section_profile .deleteBtn, #section_sign .deleteBtn', function () {
                const file = $(this).data('file');
                const isProfile = $(this).closest('#section_profile').length > 0;
                const action = isProfile ? 'deleteProfileFile' : 'deleteSignatureFile';
                const confirmDelete = confirm("Are you sure you want to delete this file?");
                
                if (confirmDelete) {
                    const that = this;
                    $.ajax({
                        url: 'ajax.php',
                        type: 'POST',
                        data: {
                            deleteFile: file,
                            action: action
                        },
                        success: function (response) {
                            const res = JSON.parse(response);
                            if (res.success) {
                                $(that).closest('.image-item, .signature-item').remove();
                            } else {
                                alert("Error: " + res.message);
                            }
                        },
                        error: function () {
                            alert("An error occurred. Please try again.");
                        }
                    });
                }
            });

            $('#infoForm').submit(function (e){
                e.preventDefault();
                var formData = new FormData(this);
                console.log(formData);
                $.ajax({
                    url: 'ajax.php', // This is the same file
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(response) {
                        $('#overlay').hide();
                        const res = JSON.parse(response);
                        if (res.success) {
                            alert("Data saved successfully!");
                            location.reload();
                        } else {
                            alert("Error resetting data: " + res.message);
                        }
                    },
                    error: function() {
                        alert("An error occurred. Please try again.");
                    }
                });
            });

            // Handle adding new field
            $('#addNewBtn').click(function(e) {
                // Add the new row to the table
                var inc = $(this).data('inc');
                const newRow = "<tr><td><input type=\"text\" name=\"field["+inc+"][name]\" placeholder=\"Enter field name\" class=\"form-control\"  /></td><td><input type=\"text\" name=\"field["+inc+"][value]\" placeholder=\"Enter field value\" class=\"form-control\" /></td><td></td></tr>";
                    inc = parseInt(inc)+1;
                $(this).data('inc',inc);
                $('#dataTable tbody').append(newRow);
            });

            // Handle reset button click
            $('#resetBtn').click(function() {
                const confirmReset = confirm("Are you sure you want to reset the data to the original CSV?");
                
                if (confirmReset) {
                    $.ajax({
                        url: 'ajax.php', // This is the same file
                        type: 'POST',
                        data: { reset: true,action: 'resetCSV' },
                        success: function(response) {
                            const res = JSON.parse(response);
                            if (res.success) {
                                location.reload(); // Reload the page to refresh the data
                            } else {
                                alert("Error resetting data: " + res.message);
                            }
                        },
                        error: function() {
                            alert("An error occurred. Please try again.");
                        }
                    });
                }
            });

            $('#uploadMediaForm').submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url: 'ajax.php', // This is the same file
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(response) {
                        $('#overlay').hide();
                        const res = JSON.parse(response);
                        if (res.success) {
                            alert("Profile/Signature uploaded successfully!");
                        } else {
                            alert("Error resetting data: " + res.message);
                        }
                    },
                    error: function() {
                        alert("An error occurred. Please try again.");
                    }
                });
            });

            //test data
            const $standard = $('#standardData');
            const $character = $('#characterData');
            const $random = $('#randomData');
            const $edit = $('#editTestData');
            const $tbody = $('#dataTable tbody');
            const $addNewBtn = $('#addNewBtn');
            const $toggleBtn = $('#toggleTableBtn');
            const $olFont = $('#fonts ol');
            const $toggleBtnFont = $('#toggleTableBtnFont');
            const initialDisplayCount = 5;

            function updateAddButtonIndex() {
                const rowCount = $tbody.find('.data-row').length;
                $addNewBtn.attr('data-inc', rowCount);
            }

            function applyToggleLogic() {
                const $rows = $tbody.find('.data-row');
                $rows.slice(initialDisplayCount).hide();
                $toggleBtn.text('Show All Fields');

                $toggleBtn.off('click').on('click', function () {
                    const $hiddenRows = $rows.slice(initialDisplayCount);
                    if ($hiddenRows.is(':visible')) {
                        $hiddenRows.slideUp();
                        $(this).text('Show All Fields');
                    } else {
                        $hiddenRows.slideDown();
                        $(this).text('Show Less Fields');
                    }
                });

                if ($rows.length <= initialDisplayCount) {
                    $toggleBtn.hide();
                } else {
                    $toggleBtn.show();
                }
            }

            function applyEditMode() {
                const editable = $edit.is(':checked');
                $tbody.find('input[name$="[name]"], input[name$="[value]"]').prop('readonly', !editable);
            }

            function loadTableRows(type) {
                $('#data_type').val(type);
                $.get(?type=${type}&ajax=1, function (html) {
                    $tbody.html(html);
                    updateAddButtonIndex();
                    applyToggleLogic();
                    applyEditMode(); // Apply edit mode after loading
                }).fail(function () {
                    $tbody.html('<tr><td colspan="3">Error loading data</td></tr>');
                });
            }

            function handleCheckboxChange() {
                if ($('#standardData').is(':checked')) {
                    $('#characterData').prop('checked', false);
                    $('#randomData').prop('checked', false);
                    $('#data_type').val('standard'); // update hidden input
                    loadTableRows('standard');
                } else if ($('#characterData').is(':checked')) {
                    $('#standardData').prop('checked', false);
                    $('#randomData').prop('checked', false);
                    $('#data_type').val('character'); // update hidden input
                    loadTableRows('character');
                } else if ($('#randomData').is(':checked')) {
                    $('#standardData').prop('checked', false);
                    $('#characterData').prop('checked', false);
                    $('#data_type').val('random'); // update hidden input
                    loadTableRows('random');
                } else {
                    $('#data_type').val('');
                    tbody.innerHTML = '';
                }
            }

            // Checkbox logic
            $('.test-group').on('change', handleCheckboxChange);
            $edit.on('change', applyEditMode);

            // Initial load
            handleCheckboxChange();


            function applyToggleLogicToFonts() {
                const $rows = $olFont.find('.font-row');
                $rows.slice(initialDisplayCount).hide();
                $toggleBtnFont.text('Show All Fonts');

                $toggleBtnFont.off('click').on('click', function () {
                    const $hiddenRows = $rows.slice(initialDisplayCount);
                    if ($hiddenRows.is(':visible')) {
                        $hiddenRows.slideUp();
                        $(this).text('Show All Fonts');
                    } else {
                        $hiddenRows.slideDown();
                        $(this).text('Show Less Fonts');
                    }
                });

                if ($rows.length <= initialDisplayCount) {
                    $toggleBtnFont.hide();
                } else {
                    $toggleBtnFont.show();
                }
            }
            applyToggleLogicToFonts();
        });
        $(function () {
            const $checkbox = $('#sameBothSides');
            const $reverseBtn = $('#reverseBtn');

            function toggleReverseButton(disable) {
                $reverseBtn.prop('disabled', disable);
                $reverseBtn.toggleClass('disabled', disable);
            }

            // Initial check on page load
            toggleReverseButton($checkbox.is(':checked'));

            // Event listener
            $checkbox.on('change', function () {
                toggleReverseButton($(this).is(':checked'));
            });
        });

        //delete pdf
        $(document).on('click', '.delete-pdf', function () {
            const filePath = $(this).data('path');
            const id = $(this).data('id');
            const confirmDelete = confirm("Are you sure you want to delete this file?");

            if (confirmDelete) {
                $.ajax({
                    url: 'ajax.php',
                    type: 'POST',
                    data: { action: 'deletePdf', deleteFile: filePath, id: id },
                    success: function (response) {
                        const res = JSON.parse(response);
                        if (res.success) {
                            alert('Deleted successfully');
                            location.reload();
                        } else {
                            alert('Delete failed: ' + (res.error || 'Unknown error'));
                        }
                    }
                });
            }
        });

        function scrollSlider(direction, id) {
            const slider = document.getElementById(id);
            const scrollAmount = 140; // adjust to match image width + margin
            slider.scrollBy({
                left: direction === 'left' ? -scrollAmount : scrollAmount,
                behavior: 'smooth'
            });
        }
        //message
        function showMessage(message, type = 'success') {
            $('#uploadMessage').html(`
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `);
            setTimeout(() => {
                $('.alert').alert('close');
            }, 8000);
        }

        // Profile image upload
        $('#imageInput').on('change', function (e) {
            e.preventDefault();
            $('#overlay').show();
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('uploadProfile', true);
            formData.append('image', file);

            $.ajax({
                url: 'ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('#overlay').hide();
                    const res = JSON.parse(response);
                    if (res.success) {
                        $('#success-message-info').html('Image Uploaded sucessfully');
                        const newId = 'profile_id_' + $('.image-item').length;
                        const imageItem = $(`
                            <div class="image-item text-center image-container">
                                <input type="radio" class="d-none bgimagesradio" id="${newId}" name="profile" value="${res.filename}" />
                                <label for="${newId}" class="image-label position-relative d-inline-block">
                                    <div class="img-wrapper">
                                        <img src="${res.filepath}" class="img-fluid bgradioscheckbox"/>
                                    </div>
                                    <span class="delete-wrapper">
                                        <button type="button" class="btn btn-danger deleteBtn" data-file="${res.filename}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </span>
                                </label>
                            </div>
                        `);

                        $('#image-slider').append(imageItem);
                        $('input[name="profile"]').prop('checked', false).removeClass('active');
                        imageItem.find('input[name="profile"]').prop('checked', true).addClass('active');
                        // $('input[name="profile"]').prop('checked', false);
                        // imageItem.find('input[name="profile"]').prop('checked', true);
                        showMessage('Profile image uploaded successfully!', 'success');
                    } else {
                        showMessage(res.message, 'danger');
                    }
                },
                error: function () {
                    alert('Upload failed.');
                }
            });

            $(this).val('');
        });

        // Signature upload
        $('#signatureInput').on('change', function (e) {
            $('#overlay').show();
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('uploadSignature', true);
            formData.append('image', file);

            $.ajax({
                url: 'ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('#overlay').hide();
                    const res = JSON.parse(response);
                    if (res.success) {
                         $('#success-message-info').html('Signature Uploaded sucessfully');
                        const newId = 'signature_id_' + $('.signature-item').length;
                        const signatureItem = $(`
                            <div class="signature-item text-center">
                                <input type="radio" class="d-none bgsignradio" id="${newId}" name="signature" value="${res.filename}" checked />
                                <label for="${newId}" class="image-label position-relative d-inline-block">
                                    <img src="${res.filepath}" class="img-fluid bgsigncheckbox" />
                                    <span class="delete-wrapper">
                                        <button type="button" class="btn btn-danger deleteBtn" data-file="${res.filename}"><i class="fa-solid fa-trash"></i></button>
                                    </span>
                                </label>
                            </div>
                        `);
                        $('#signature-slider').append(signatureItem);
                        $('input[name="signature"]').prop('checked', false).removeClass('active');
                        signatureItem.find('input[name="signature"]').prop('checked', true).addClass('active');
                        // $('input[name="signature"]').prop('checked', false);
                        // signatureItem.find('input[name="signature"]').prop('checked', true);
                        //$('#signature-slider .text-end').before(signatureItem);
                        showMessage('Signature uploaded successfully!', 'success');
                    } else {
                        showMessage(res.message, 'danger');
                    }
                },
                error: function () {
                    alert('Upload failed.');
                }
            });

            $(this).val('');
        });

        //face reverse
        $(document).ready(function () {
            const $faceBtn = $('#faceBtn');
            const $reverseBtn = $('#reverseBtn');
            const $faceBox = $('#faceImageBox');
            const $reverseBox = $('#reverseImageBox');
            const $sameBoth = $('#sameBothSides');

            // Switch tab
            $faceBtn.on('click', function () {
                $faceBtn.addClass('active');
                $reverseBtn.removeClass('active');
                $faceBox.removeClass('d-none');
                $reverseBox.addClass('d-none');
            });

            $reverseBtn.on('click', function () {
            $reverseBtn.addClass('active');
            $faceBtn.removeClass('active');
            $faceBox.addClass('d-none');
            $reverseBox.removeClass('d-none');

            // If same selected, sync checkboxes
            if ($sameBoth.is(':checked')) {
                syncFaceToReverse();
            }
        });

        $sameBoth.on('change', function () {
            if ($(this).is(':checked')) {
                syncFaceToReverse();
            }
        });

        function syncFaceToReverse() {
            const $faceCheckboxes = $('input.bgimagescheckbox.face');
            const $reverseCheckboxes = $('input.bgimagescheckbox.reverse');
            $reverseCheckboxes.prop('checked', false);

            $faceCheckboxes.each(function (i) {
                if ($(this).is(':checked')) {
                    $reverseCheckboxes.eq(i).prop('checked', true);
                }
            });
        }

        //run svg test
        $('#runSvgTestBtn').on('click', function () {
            $.ajax({
                type: 'POST',
                url: 'ajax.php', // adjust if path is different
                data: { action: 'run_svg_test' },
                dataType: 'json',
                success: function (response) {
                    const container = $('#testResultContainer');
                    container.empty();

                    const row = $('<div class="row"><h4 class="card-title text-danger">Test Results</h4></div>');

                    // SVG ID Results (Left Side)
                    const colLeft = $('<div class="col-6"></div>');
                    $.each(response.results, function (label, res) {
                        const unmatchedCount = res.unmatched.length;
                        const unmatchedFields = res.unmatched.map(id => <code class="small">id = ${id}</code>).join('<br>');

                        const html = `
                            <details class="mb-3">
                                <summary><strong>${label} SVG</strong> — ${res.total} IDs, ${res.matched.length} matched</summary>
                                <div class="mt-2">
                                    <p class="mb-1 small">${unmatchedCount} field${unmatchedCount !== 1 ? 's' : ''} have no matching label</p>
                                    ${unmatchedFields}
                                </div>
                            </details>
                        `;
                        colLeft.append(html);
                    });

                    // Fonts (Right Side)
                    const colRight = $('<div class="col-6"></div>');
                    $.each(response.fonts, function (label, fontData) {
                        const fontCount = fontData.fonts.length;
                        const matchedCount = fontData.matched.length;
                        const missingCount = fontData.missing.length;
                        const collapseId = 'fontList' + label.replace(/\s+/g, '');

                        const fontListItems = fontData.fonts.map(f => <li>${f}</li>).join('');
                        const missingFonts = fontData.missing.map(f => <li>${f}</li>).join('');

                        const html = `
                            <details class="mb-3">
                                <summary class="fw-bold">${label} SVG Fonts</summary>
                                <div class="mt-2 ms-3">
                                    <p class="mb-1 small">
                                        This SVG uses ${fontCount} font${fontCount !== 1 ? 's' : ''} —
                                        <strong>${matchedCount}</strong> on server,
                                        <strong>${missingCount}</strong> missing.
                                        <a class="ms-2 text-decoration-none" data-bs-toggle="collapse" href="#${collapseId}" role="button" aria-expanded="false" aria-controls="${collapseId}">
                                            Show Svg Fonts
                                        </a>
                                    </p>
                                    <div class="collapse" id="${collapseId}">
                                        <ul class="mb-0 ps-3">${fontListItems}</ul>
                                    </div>
                                </div>
                            </details>
                        `;
                        colRight.append(html);
                    });

                    row.append(colLeft).append(colRight);
                    container.append(row);
                },
                error: function (xhr, status, error) {
                    alert('Error: ' + xhr.responseText);
                }
            });
        });

        function refreshReverseBorders() {
            $('input.bgimagescheckbox.reverse').each(function () {
                const $img = $(this).next('label.image-label').find('img.selectedImages');
                if (this.checked) {
                    $img.addClass('active');
                } 
            });
        }
        
        // Limit reverse images count = face images count
        $('input.bgimagescheckbox.reverse').on('change', function () {
            if (!$sameBoth.is(':checked')) {
                const faceCount = $('input.bgimagescheckbox.face:checked').length;
                const reverseChecked = $('input.bgimagescheckbox.reverse:checked').length;

                if (reverseChecked > faceCount) {
                this.checked = false;

                $(this)
                    .next('label.image-label')
                    .find('img.selectedImages')
                    .removeClass('active')

                alert(You can only select up to ${faceCount} reverse images.);
                }
            }
            refreshReverseBorders();
        });

        $('#uploadFontBtn').on('click', function () {
            $('#fontFileInput').click();
        });

        $('#fontFileInput').on('change', function () {
            const formData = new FormData();
            formData.append('fontfile', this.files[0]);
            formData.append('action', 'handleFontUploadAndList');

            $.ajax({
                url: 'ajax.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                // success: function (response) {
                //     if (response.success && response.filename) {
                //         let msg = Font uploaded: ${response.filename};

                //         if (response.font_family) {
                //             msg += \nFont family: ${response.font_family};
                //         }

                //         if (response.installed !== undefined) {
                //             msg += \nInstalled: ${response.installed ? 'Yes' : 'No'};
                //         }

                //         alert(msg);

                //         const uploadedPath = 'fonts/' + response.filename;
                //         $('<p>')
                //             .text(Uploaded: ${uploadedPath} | Family: ${response.font_family || 'N/A'} | Installed: ${response.installed ? 'Yes' : 'No'})
                //             .appendTo('#uploadedFonts');

                //     } else if (response.error) {
                //         alert('Error: ' + response.error);
                //     }
                // },
                success: function (response) {
                    showMessage('Font uploaded successfully!', 'success');
                if (response.success && response.filename) {
                    // Append the new font to the existing font list ol
                    const $fontList = $('.list-group.list-group-numbered');
                    $('<li>')
                        .addClass('list-group-item font-row')
                        .text(`${response.font_family || response.filename} `)
                        // (Installed: ${response.installed ? 'Yes' : 'No'})
                        .appendTo($fontList);

                } else if (response.error) {
                    alert('Error: ' + response.error);
                }
                },

                error: function (xhr) {
                    console.log(xhr.responseText);
                    alert('Upload failed. Please try again.');
                }
            });
        });
    });
    </script>
    <?php
        function loadCsvData($filePath) {
            $data = [];

            if (!file_exists($filePath)) {
                return $data;
            }

            if (($handle = fopen($filePath, 'r')) !== false) {
                $firstRow = true;
                while (($row = fgetcsv( $handle,1000,",","\"","\\")) !== false) {
                    if ($firstRow) {
                        $firstRow = false;
                        continue;
                    }
                    if (isset($row[0], $row[1])) {
                        $data[$row[0]] = $row[1];
                    }
                }
                fclose($handle);
            }

            return $data;
        }
    ?>
    </body>
</html>