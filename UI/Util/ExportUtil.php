<?php

class ExportUtil {
	public static function generateCSV($columns,$data){
		$filePath = "table.csv";
		$fh = fopen($filePath,'w+');
		fwrite($fh,implode(',',$columns));
		fwrite($fh,"\n");
		foreach($data as $row){
			$str = "";
			foreach($columns as $col){
				if(isset($row[$col]))
				$str .= $row[$col] . ",";
				else{
					$str .= " - ,";
				}
			}
			$str = substr($str,0,-1);
			fwrite($fh,$str."\n");
		}
		fclose($fh);
		header("Content-Disposition: attachment; filename=" . urlencode('table.csv'));
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Description: File Transfer");
		header("Content-Length: " . filesize($filePath));
		readfile($filePath);
	}
	public static function generatePDF($header,$data){
		if(!class_exists('FPDF')){
			require_once dirname(__FILE__).'/../../lib/fpdf16/fpdf.php';
		}
		$pdf = new  FPDF();
		$pdf->SetFont('Arial','',14);
		$pdf->AddPage();

		//Colors, line width and bold font
		$pdf->SetFillColor(255,0,0);
		$pdf->SetTextColor(255);
		$pdf->SetDrawColor(128,0,0);
		$pdf->SetLineWidth(.3);
		$pdf->SetFont('','B');
		//Header
		for($i=0;$i<count($header);$i++)
		$pdf->Cell('25',7,$header[$i],1,0,'C',true);
		$pdf->Ln();
		//Color and font restoration
		$pdf->SetFillColor(224,235,255);
		$pdf->SetTextColor(0);
		$pdf->SetFont('');
		//Data
		$fill=false;
		foreach($data as $row)
		{
			foreach($row as $d)
			$pdf->Cell('25',6,$d,'LR',0,'L',$fill);
			$pdf->Ln();
			$fill=!$fill;
		}
		$pdf->Output();

	}
}