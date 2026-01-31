<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

class Uploads extends Controller
{
    public function index(...$path)
    {
        if (!session()->get('is_logged_in')) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $filePath = WRITEPATH . 'uploads/' . implode('/', $path);

        if (!is_file($filePath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $file = new \CodeIgniter\Files\File($filePath);
        $mimeType = $file->getMimeType();

        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setContentType($mimeType)
            ->setBody(file_get_contents($filePath));
    }
}
