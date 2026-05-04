<?php

abstract class Controller
{
    protected function view($view, $data = [])
    {
        extract($data);
        $viewFile = BASE_PATH . '/app/views/' . str_replace('.', '/', $view) . '.php';
        
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            die("View $view not found.");
        }
    }
    
    protected function redirect($url)
    {
        header("Location: $url");
        exit();
    }
}
