<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Tableau de bord (page protégée : hérite de Back_Controller)
 */
class DashboardC extends Back_Controller
{
    public function index()
    {
        $data = ['title' => 'Tableau de bord'];

        $this->load->view('back/layouts/app/header', $data);
        $this->load->view('back/layouts/app/sidebar', $data);
        // $this->load->view('back/components/app/dashboard', $data);
        $this->load->view('back/components/app/dashboard/dashboard', $data);
        $this->load->view('back/layouts/app/footer', $data);
    }
}
