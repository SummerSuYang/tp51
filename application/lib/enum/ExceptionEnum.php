<?php

namespace app\lib\enum;

class ExceptionEnum
{
    const BASE = 10000;
    //BaseException
    const AUTH = 11000;
    //TokenException
    const TOKEN = 12000;
    //ParamsException
    const PARAMS = 13000;
	 //UploadFileException
    const UPLOAD_FILE = 14000;
}