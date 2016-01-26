<?php
class ExcelToArrary {

    public function __construct() {
		vendor("Excel.PHPExcel", LIB_PATH.'../Extend/Vendor');
		vendor("Excel.PHPExcel.IOFactory", LIB_PATH.'../Extend/Vendor');
    }
    
	public function read($filename,$encode,$file_type){
	    if(strtolower ( $file_type )=='xls')//判断excel表类型为2003还是2007
        {
            vendor("Excel.PHPExcel.Reader.Excel5", LIB_PATH.'../Extend/Vendor'); 
            $objReader = PHPExcel_IOFactory::createReader('Excel5');
        }else if(strtolower ( $file_type )=='xlsx')
        {
            vendor("Excel.PHPExcel.Reader.Excel2007", LIB_PATH.'../Extend/Vendor'); 
            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        }
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($filename);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelData = array();
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $excelData[$row][] =(string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            }
        }
        return $excelData;
      }
}
?>