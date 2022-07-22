<?php
$toEmails 	= array_values(explode(',',getenv('TO_EMAIL')));
$host 		= getenv('DB_HOST');
$user	 	= getenv('DB_USER');
$pass 		= getenv('DB_PASSWORD');
$db 		= getenv('DB_NAME');
$smtp_host 	= getenv('SMTP_HOST');
$username 	= getenv('SMTP_USER');
$password 	= getenv('SMTP_PASSWORD');
$from_env 	= explode(',', getenv('FROM_EMAIL'));
$from	 	= array( $from_env[0] => $from_env[1]);

date_default_timezone_set('Asia/Ho_Chi_Minh');

require_once 'vendor/autoload.php';
// 1. Collect database
$con = mysqli_connect($host, $user, $pass, $db);
$date_from = date('Y-m-d', strtotime("-1 days"));
$date_to = date('Y-m-d');
$subject = "Report Vnpay Date: $date_from";
$sql = "SELECT * FROM `PaymentTransactions` WHERE status=1 and LastUpdate > '$date_from' and LastUpdate < '$date_to'";
echo "$sql\n";
#die;
$res = mysqli_query($con, $sql);

if (mysqli_num_rows($res) == 0 )
{
    echo date( '[Y-m-d H:i:s]') . " - No data\r\n";
}

// 2. Export Excel
$objPHPExcel = new PHPExcel();
$objPHPExcel	=	new	PHPExcel();

$objPHPExcel->setActiveSheetIndex(0);

$cols = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];

$objPHPExcel->getActiveSheet()->getStyle("A1:E1")->getFont()->setBold(true);

$rowCount	=	2;
$amount = 0;
while ($row = mysqli_fetch_assoc($res)) {
	if ($rowCount == 2) {
		$colName = 0;
		foreach ($row as $key=>$value) {
			$objPHPExcel->getActiveSheet()->SetCellValue($cols[$colName].'1', $key);
			$colName ++;
		}
	}
	$colCount = 0;
	foreach ($row as $key=>$value) {
		$objPHPExcel->getActiveSheet()->SetCellValue($cols[$colCount].$rowCount, mb_strtoupper($value,'UTF-8'));
		$colCount ++;
	}
    $rowCount++;
}

// auto size
foreach (range('A', $objPHPExcel->getActiveSheet()->getHighestDataColumn()) as $col) {
        $objPHPExcel->getActiveSheet()
                ->getColumnDimension($col)
                ->setAutoSize(true);
    }

$objWriter	=	new PHPExcel_Writer_Excel2007($objPHPExcel);
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$fileName = "export_$date_from.xlsx";
$objWriter->save($fileName);

// Create the Transport
$transport = (new Swift_SmtpTransport($smtp_host, 465, 'ssl'))
    ->setUsername($username)
    ->setPassword($password);

// Create the Mailer using your created Transport
$mailer = new Swift_Mailer($transport);


$htmlContent = "See attachment";
// Create a message
$message = (new Swift_Message($subject))
    ->setFrom($from)
    ->setTo($toEmails)
    ->attach(Swift_Attachment::fromPath($fileName))
    ->setBody($htmlContent);

// Send the message
$mailer->send($message);
echo date('[Y-m-d H:i:s]') . " - Send report succesfully\r\n";
unlink($fileName);
