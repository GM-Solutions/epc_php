<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Welcome extends CI_Controller {

    public function index() {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Hello World !');

        $writer = new Xlsx($spreadsheet);

        $filename = 'name-of-the-generated-file.xlsx';

        $writer->save($filename); // will create and save the file in the root of the project
    }

    public function download_new() {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('bajaj')
                ->setLastModifiedBy('gladminds')
                ->setTitle('Bajaj EPC')
                ->setSubject('Show vehical applicable parts')
                ->setDescription('Part catlog');


        $styleArray = array(
            'font' => array('bold' => true,),
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,),
            'fill' => array(
                'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                'rotation' => 90,
                'startcolor' => array('argb' => 'FFA0A0A0',), 'endcolor' =>
                array('argb' => 'FFFFFFFF',),));
        $spreadsheet->getActiveSheet()->getStyle('A1:F1')->applyFromArray($styleArray);

        foreach (range('A', 'F') as $columnID) {
            $spreadsheet->getActiveSheet()->getColumnDimension($columnID)
                    ->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue("A1", 'Username')
                ->setCellValue("B1", 'Name')
                ->setCellValue("C1", 'UserEmail')
                ->setCellValue("D1", 'UserAddress')
                ->setCellValue("E1", 'UserJob')
                ->setCellValue("F1", 'Gender');

        /* add data */

        $x = 2;
        foreach ($subscribers as $sub) {
            $spreadsheet->setActiveSheetIndex(0)
                    ->setCellValue("A$x", $sub['user_username'])
                    ->setCellValue("B$x", $sub['user_name'])
                    ->setCellValue("C$x", $sub['gender'])
                    ->setCellValue("D$x", $sub['user_email'])
                    ->setCellValue("E$x", $sub['user_address'])
                    ->setCellValue("F$x", $sub['user_job']);
            $x++;
        }


        $spreadsheet->getActiveSheet()->setTitle('BAJAJ PART CATLOGUE');
        $spreadsheet->setActiveSheetIndex(0);



        $writer = new Xlsx($spreadsheet);

        $filename = 'name-of-the-generated-file';

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output'); // download file 
    }

    public function download() {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Hello World !');

        $writer = new Xlsx($spreadsheet);

        $filename = 'name-of-the-generated-file';

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output'); // download file 
    }

}
