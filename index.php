<?php

/**
 * 
 * This script open a stream file downloader in Pure PHP
 * Its possible to edit the, per ex., Http response headers before streaming
 * Or implement some control access to the hosted files.
 * 
 * Streaming allow also large files to be downloaded without crash your server. :D
 * 
 * In the example below was implementad a way to download hash named files.
 * But get it with right original file name.
 * It is done throught the URL query parameter "filename" Ex:
 * 
 * with URL:
 * https://site.mydomain.com/files/919596eb-e188-4346-a440-b42a8b1800fe.JPG?filename=example_image.jpg
 * 
 * Will download from public file: "919596eb-e188-4346-a440-b42a8b1800fe.JPG"
 * but on client size will get a file named: "example_image.jpg"
 * 
 * Note: This script works together with mod_rewrite in a .htaccess file,
 * that rewrite all URLs to this index.php file. Ex. below:
 * 
 * # .htaccess files example:
 * RewriteEngine On
 * RewriteBase /
 * RewriteRule ^(.+)$ index.php
 * 
 * Author: Vagner Panarello [vpanarello@gmail.com]
 * 
 */

Class File {

    public const PATH = '';

    public $name = null;
    public $hashname = null;
    public $mimetype = null;
    public $size = null;
    public $bytesDown = 0;


    function __construct($hashname, $name = null)
    {
        if(!file_exists(static::PATH.$hashname))
            user_error('File locally not found');

        $this->hashname = $hashname;
        $this->name = $hashname;
        $this->mimetype = mime_content_type(static::PATH.$hashname);
        $this->size = filesize(static::PATH.$hashname);

        if($name)
            $this->name = $name;
    }
    function path()
    {
        return static::PATH.$this->hashname;
    }

    function readStream()
    {
        return fopen($this->path(), "rb");
    }
}


Class Request {

    protected function getRequestedURL()
    {
        return $_SERVER[REQUEST_URI];
    }

    public function getHashname()
    {
        return explode("?", explode("/", $this->getRequestedURL())[2])[0];
    }

    protected function fileNotFound()
    {
        header('Location: '.'https://tandler.com');
        exit();
    }

    public function getFilename()
    {
        return $_GET['filename'];
    }

    public function getFile()
    {
            if(!file_exists(File::PATH.$this->getHashname()))
            {
                $this->fileNotFound();
                return null;

            }

            return new File($this->getHashname(), $this->getFilename());
    }

}

Class Response {

    protected const DEFAULT_HEADERS = [
        'Content-Description'       => 'File Transfer',
        'Content-Transfer-Encoding' => 'binary',
        'Expires'                   => '0',
        'Cache-Control'             => 'must-revalidate, post-check=0, pre-check=0',
        'Pragma'                    => 'public',
      ];

    /** Chunk Size */
    protected const CHUNK = 8192;

    protected $headers = [];

    protected $file = null;

    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function download(File $file)
    {
        $this->file = $file;
        return $this;
    }

    protected function sendHeaders()
    {

        foreach(array_merge(static::DEFAULT_HEADERS, $this->headers) as $key=>$value)
        {
            //echo '<p>'.$key.': '.$value.'</p>';
            header($key.': '.$value);
        }
        return $this;
    }

    protected function writeStream(File $file)
    {
        $rStream = $file->readStream();

        $sent = -1;
        $chunk = 0;

        /* Open file and Stream */
        while (!feof($rStream) and (connection_status()==0)) {
            $chunk = fread($rStream, static::CHUNK);
            print($chunk);
            flush();
            $sent++;
        }

        fclose($rStream);
        $file->bytesDown = $sent * static::CHUNK;
    }

    public function answer()
    {
        ob_end_clean();
        $this->sendHeaders();
        if($this->file)
            $this->writeStream($this->file);
        ob_end_flush();
    }
}

/**
 * Main function where the logic Request-Response is done
 */
function execute(Request $request, Response $reponse)
{
    $file = $request->getFile();

    $reponse->setHeader('Content-Type', $file->mimetype)
        ->setHeader('Content-Length', $file->size)
        ->setHeader('Content-Disposition', 'attachment; filename="'.basename($file->name).'"')
        ->download($file);

    return $reponse;
}

execute(new Request(), new Response())->answer();
exit();