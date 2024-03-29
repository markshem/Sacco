<?php
include 'include/configs.php';
require_once('tcpdf/tcpdf.php');
//INCIATE TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetAuthor(Company_Name);
$pdf->setTitle(Company_Name);

$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->setHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, Company_Name, Company_Info);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->setHeaderMargin(PDF_MARGIN_HEADER);
$pdf->setFooterMargin(PDF_MARGIN_FOOTER);
$pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
  require_once(dirname(__FILE__) . '/lang/eng.php');
  $pdf->setLanguageArray($l);
}
$pdf->setFontSubsetting(true);
$pdf->setFont('dejavusans', '', 10, '', true);
$pdf->setTextShadow(array('enabled' => true, 'depth_w' => 0.2, 'depth_h' => 0.2, 'color' => array(196, 196, 196), 'opacity' => 1, 'blend_mode' => 'Normal'));
$pdf->SetFont('helvetica', 'B', 10);
$pdf->AddPage();
//INCLUDE DB
include 'db_connect.php' ;
$refno = $_GET['refno'];
//GET LOAN LIST
$getif = mysqli_query($db, "SELECT * FROM loan_list WHERE ref_no = '$refno'");
$data = mysqli_fetch_array($getif);
$borrowerid = $data['borrower_id'];
$loanid = $data['loan_type_id'];
$loanamount = $data['amount'];
$purpose = $data['purpose'];
$planid = $data['plan_id'];
//GET BORROWER DETAILS
$getborrower = mysqli_query($db, "SELECT * FROM borrowers WHERE id = '$borrowerid'");
$borrower = mysqli_fetch_array($getborrower);
$borrowerfirstname = $borrower['firstname'];
$borrowermiddlename = $borrower['middlename'];
$borrowerlastname = $borrower['lastname'];
$fullnames = $borrowerfirstname . ' ' . $borrowermiddlename . ' ' . $borrowerlastname;
$borroweremail = $borrower['email'];
$borroweraddress = $borrower['address'];
$borrowertaxid = $borrower['tax_id'];
$borrowerphone = $borrower['contact_no'];
//GET LOAN TYPE
$getloan = mysqli_query($db, "SELECT * FROM loan_types WHERE id = '$loanid'");
$loan = mysqli_fetch_array($getloan);
$loantype = $loan['type_name'];
//GET PLAN DETAILS
$getplan = mysqli_query($db, "SELECT * FROM  loan_plan WHERE id = '$planid'");
$plan = mysqli_fetch_array($getplan);
$interestpercentage = $plan['interest_percentage'];
$interestamount = $loanamount * $interestpercentage / 100;
$interestamount = number_format($interestamount, 2);
$interestamountpaid = $interestamount + $loanamount;
//status
$status = $data['status'];
if ($status == 0) {
  $status = 'For Approval';
}elseif ($status == 1) {
  $status = 'Approved';
}elseif ($status == 2) {
  $status = 'Released';
}elseif ($status == 3) {
  $status = 'Completed';
}elseif ($status == 4) {
  $status = 'Denied';
}

$html = <<<EOD
<style>
@import url("https://fonts.googleapis.com/css2?family=Poppins:wght@200&display=swap");
body {
  background-color: #f5f5f5;
}
.customers {
    font-family: "Poppins", sans-serif;
    width: 100%;
    border: 1px solid #ccc;
  }
  .customers td{
    border: 0.25px solid black;
    padding: 30px;
  font-weight: normal;
  }
  .customers th {
    border: 1px solid white;
    padding: 8px;
    text-align: center;
    background: #4CAF50;
    color: white;
    font-weight: bold;
  }
  .customers tr:nth-child(even) {
    background-color: black;
  }
  

  .customers tr:hover {
    background-color: #ddd;
  }
  
  .customers th {
    padding-top: 5px;
    padding-bottom: 5px;
  
    background-color: green;
    color: white;
    font-size:14px;
  }
.infocard{
    font-family: "Poppins", sans-serif;
    font-size:10px;
    color: black;
    text-align: left;
}
.dec{
    width: 80%;
}

.tot{
    width: 20%;
    text-align: center;
}
.info{
    display: flex;
    justify-content: space-between;
    padding: 5px;
}

.info1{
    width: 50%;
    background-color: #f5f5f5;
}

.info2{
    width: 50%;
}

.recipt{
    margin-top: 1%;
}

p span{
    font-weight: none;
}
.title{
    font-size: 20px;
    font-weight: bold;
    text-align: center;
    text-transform: uppercase;
}
.totals td {
  border-top: 2px solid #ccc;
  font-weight: bold;
  padding: 10px;
  text-align: right;
}

.amount{
    text-align: center;
}
.total{
    text-align: center;
    color: green;
}

.paid {
  font-weight: bold;
  text-transform: uppercase;
  color: green;
  width: 100px;
}

.unpaid {
  font-weight: bold;
  text-transform: uppercase;
  color: #f44336;
}
tfoot{
    text-align: left;
}
</style>
<div class="recipt">
<div class="infocard">
<p class="title">LOAN RECIPT</p>

<div class="info1">
<p >Reference  No: <span>$refno</span></p>
</div>
<div class="info2">
<h3>Invoiced To</h3>
<p>Name: <span>$fullnames</span></p>
<p>Phone: <span>$borrowerphone</span></p>
<p>Email: <span>$borroweremail</span></p>
<p>Address: <span>$borroweraddress</span></p>
<p>Tax ID: <span>$borrowertaxid</span></p>
</div>
</div>
<table class="customers">
<tr>
<th>Loan Type</th>
<th >Loan Amount</th>
<th>Loan Intrest</th>
</tr>
<tr>
<td>$loantype</td>
<td class="amount">Ksh  $loanamount</td>
<td class="amount">Ksh  $interestamount</td>
</tr>
<tfoot>
<tr class="totals">
<td>Total</td>
<td class="total">KSh $interestamountpaid</td>
</tr>
<tr class="totals">
<td>Status</td>
<td>
<span class="paid">$status</span>
</td>
</tr>
</tfoot>
</table>



<p><span style="text-align: center; margin-top:40px;">PDF Generated on 15/01/2023</span></p>


</div>
EOD;
$pdf->writeHTML($html, true, false, true, false, '');
// <span class="unpaid">Unpaid</span>
//Close and output PDF document
$pdf->Output('invoiceexample.pdf', 'I');