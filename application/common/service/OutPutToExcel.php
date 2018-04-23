<?php

namespace app\common\service;

use think\Exception;

class OutPutToExcel
{
    public $data;
    public $phpExcel;
    public $activeSheet;
    public $column=[];
    public $key;
    public $field;
    protected static $instance;

    /**
     * OutPutToExcel constructor.
     * @param $data
     * @throws \PHPExcel_Exception
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->phpExcel = new \PHPExcel();
        $this->activeSheet = $this->phpExcel->getActiveSheet();
    }

    /**
     * @param $title
     * @return $this
     */
    public function setSheetTitle($title)
    {
        $this->activeSheet->setTitle($title);
        return $this;
    }

    /**
     * @param $field
     * @return $this
     * @throws \PHPExcel_Exception
     */
    public function handle($field)
    {
        $this->writeField($field);
        $this->writeRow();
        return $this;
    }

    /**
     * @param array $field
     * @throws \PHPExcel_Exception
     */
    public function writeField(array $field)
    {
		if(empty($this->data)) return;
		//展示在excel表中的字段名，一般都是汉字
		$this->field = $field;
        //数据中的字段名,用于获取数组中的值
        $this->key = array_keys($this->data[0]);

        for($i=0;$i<count($this->field);$i++)
        {
            $column = \PHPExcel_Cell::stringFromColumnIndex($i);
            array_push($this->column,$column);
            $position = $column.'1';

            //写入数据
            $this->activeSheet->setCellValue($position,$this->field[$i]);

            $this->fieldStyle($column);
        }
    }

    /**
     * @param $column
     * @throws \PHPExcel_Exception
     */
    public function fieldStyle($column)
    {
        //设置宽度自动
        $this->activeSheet->getColumnDimension($column)->setWidth(20);

        $position = $column.'1';
        $this->activeSheet->getStyle($position)->getFont()->setSize(12);
        $this->activeSheet->getStyle($position)->getFont()->setBold(true);
        $this->activeSheet->getStyle($position)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->activeSheet->getStyle($position)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
    }

    /**
     * @throws \PHPExcel_Exception
     */
    public function writeRow()
    {
        $k = 1;
        foreach ($this->data as $item)
        {
            $k++;
            for($i=0;$i<count($this->key);$i++)
            {
                $position = $this->column[$i].$k;
                $value = $item[$this->key[$i]];
                $this->activeSheet->setCellValueExplicit($position,$value,\PHPExcel_Cell_DataType::TYPE_STRING);
                $this->rowStyle($position);
            }
        }
    }

    /**
     * @param $position
     * @throws \PHPExcel_Exception
     */
    public function rowStyle($position)
    {
        $this->activeSheet->getStyle($position)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->activeSheet->getStyle($position)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
    }

    /**
     * @return string
     */
    public function setName()
    {
        return uniqid();
    }

    /**
     * @param null $name
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public function outPut($name=null)
    {
        if(is_null($name)) $name = $this->setName();

        ob_end_clean();
        header("Content-Type:application/force-download");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Type:application/vnd.ms-excel');
        header('Content-Disposition:attachment;filename="'.$name.'.xlsx"');
        header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
        $objWriter = \PHPExcel_IOFactory::createWriter($this->phpExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public static function __callStatic($method, $args)
    {
        if(is_null(static::$instance))
            static::$instance = new static();

        if( !method_exists(static::$instance, $method)){
            throw new Exception("无法调用 $method 方法");
        }
        return call_user_func_array([static::$instance, $method], $args);
    }
}