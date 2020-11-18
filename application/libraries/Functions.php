<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Carbon\Carbon;

class Functions{

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('menu_model');
    }

    public function generate_menu() {
        $menus = $this->CI->menu_model->get_list_menus($this->CI->session->akses_id, 0, null);

        $menu_list = '';
        foreach($menus as $m){
            // level 0 as parent
            $menu_id = $m['id'];
            // level 1
            $menu1 = $this->CI->menu_model->get_list_menus($this->CI->session->akses_id, 1, $menu_id);
            if(count($menu1) > 0) {
                $menu_list .= '<li class="nav-item nav-item-submenu"><a href="javascript:void(0);" class="nav-link legitRipple"><i class="'.$m['icon'].'"></i><span>' . $m['menu'] . '</span></a>';
                $menu_list .= '<ul class="nav nav-group-sub" data-submenu-title="'.$m['menu'].'">';

                foreach ($menu1 as $m1) {
                    $menu_id = $m1['id'];
                    // level 2
                    $menu2 = $this->CI->menu_model->get_list_menus($this->CI->session->akses_id, 2, $menu_id);
                    if (count($menu2) > 0) {
                        $menu_list .= '<li class="nav-item nav-item-submenu"><a href="javascript:void(0);" class="nav-link legitRipple"><i class="'.$m1['icon'].'"></i><span>' . $m1['menu'] . '</span></a>';
                        $menu_list .= '<ul class="nav nav-group-sub" data-submenu-title="'.$m1['menu'].'">';

                        foreach($menu2 as $m2){

                            $menu_list .= '<li class="nav-item"><a href="' . base_url($m2['url']) . '" class="nav-link">' . $m2['menu'] . '</a></li>';
                        }
                        $menu_list .= '</ul></li>';
                    }else {
                        $menu_list .= '<li class="nav-item"><a href="' . base_url($m1['url']) . '" class="nav-link">' . $m1['menu'] . '</a></li>';
                    }
                }

                $menu_list .= '</ul></li>';
            }else{
                $menu_list .= '<li class="nav-item"><a href="' . base_url($m['url']) . '" class="nav-link legitRipple"><i class="'.$m['icon'].'"></i> <span>' . $m['menu'] . '</span></a></li>';
            }
        }

        return $menu_list;
    }

    function check_session(){
        if(!isset($this->CI->session->logged_in)){
            return redirect('logout');
        }
    }

    function check_priv($module){

        $menu = $this->CI->menu_model->get_menu_id(array('m.link' => $module ));

        $result = array();
        $arrMenu = explode(',', $menu['privileges']);
        $sExtends = array('editor_create', 'editor_edit', 'editor_remove');

        $text='[';

        for($i=0; $i < count($arrMenu); $i++){
            if($arrMenu[$i] == '1') {
                if(($i+1) == count($arrMenu))
                    $text .= '{"sExtends":"'.$sExtends[$i].'","editor":editor}';
                else
                    $text .= '{"sExtends":"'.$sExtends[$i].'","editor":editor},';
            }
        }

        $text .= ']';
        return $text;
    }

    function check_priv2($module){

        $menu = $this->CI->menu_model->get_menu_id(array('m.link' => $module ));

        return $menu;
    }

    // check access if passing module by url
    function check_access($module){
        $module = $this->CI->menu_model->get_menu_id(array('m.link' => $module ));

        $grant_access = $module['access_module'];

        if($grant_access == 0){
            show_404();
        }

    }

    // check access if passing sub by url
    function check_access2($module, $action_module){
        $action_module = strtolower($action_module);
        $module = $this->CI->menu_model->get_menu_id(array('m.link' => $module ));

        $submodule = $module['privileges'];
        $privileges = explode(',', $submodule);

        switch($action_module){
            case "add"      : $grant_access = $privileges[0]; break;
            case "edit"     : $grant_access = $privileges[1]; break;
            case "delete"   : $grant_access = $privileges[2]; break;
            default         : $grant_access = 0; break;
        }

        if($grant_access == 0){
            show_404();
        }

    }

    public function convert_date_indo($array) {
        $datetime=$array['datetime'];
        $y=substr($datetime,0,4);
        $m=substr($datetime,5,2);
        $d=substr($datetime,8,2);
        $conv_datetime=date("j/m/Y",mktime(1,0,0,$m,$d,$y));#"$d / $m / $y";
        return($conv_datetime);
    }

    public function convert_date_indo2($array) {
        $datetime=$array['datetime'];
        $y=substr($datetime,0,4);
        $m=substr($datetime,5,2);
        $d=substr($datetime,8,2);
        $conv_datetime=date("j-m-Y",mktime(1,0,0,$m,$d,$y));#"$d - $m - $y";
        return($conv_datetime);
    }

    /* ------------------------------
    // Konversi tanggal tgl indo ke sql
    //
    // Usage :  convert_date_sql("31/12/2014") return 2014-12-31
    -------------------------------*/
    public function convert_date_sql($date) {
        // list($day, $month, $year) = split('[/.-]', $date); => DEPRECATED
        list($day, $month, $year) = preg_split('/[\/\.\-]/', $date);
        return "$year-".sprintf("%02d", $month)."-".sprintf("%02d", $day);
    }

    public function check_bulan($tanggal) {
        $bulan_array=array(
            "1"=>"Januari",
            "2"=>"Februari",
            "3"=>"Maret",
            "4"=>"April",
            "5"=>"Mei",
            "6"=>"Juni",
            "7"=>"Juli",
            "8"=>"Agustus",
            "9"=>"September",
            "10"=>"Oktober",
            "11"=>"November",
            "12"=>"Desember");
        $tanggal_array=preg_split('/[\/\.\-]/', $tanggal);
        $bulan_n=date("n",mktime("1","1","1",$tanggal_array[1],$tanggal_array[2],$tanggal_array[0]));
        return $bulan_array[$bulan_n];
    }

    public function format_tgl_cetak($tanggal) {
        list($year, $month, $day) = preg_split('/[\/\.\-]/', $tanggal);
        return intval($day)." ".$this->check_bulan($tanggal)." ".$year;
    }

    public function get_bulan_string($bln) {
        switch($bln) :
            case 1: 
                $text = "Januari";
                break;
            case 2: 
                $text = "Februari";
                break;
            case 3: 
                $text = "Maret";
                break;
            case 4: 
                $text = "April";
                break;
            case 5: 
                $text = "Mei";
                break;
            case 6: 
                $text = "Juni";
                break;
            case 7: 
                $text = "Juli";
                break;
            case 8: 
                $text = "Agustus";
                break;
            case 9: 
                $text = "September";
                break;
            case 10: 
                $text = "Oktober";
                break;
            case 11: 
                $text = "November";
                break;
            case 12: 
                $text = "Desember";
                break;
        endswitch;

        return $text;
    }

    function generate_tiket($id) {
        return date('YmdHis') . '-' . $id;
    }

}
