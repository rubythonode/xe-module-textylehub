<?php
    /**
     * @class  textylehub
     * @author zero (skklove@gmail.com)
     * @brief  textylehub 모듈의 controller class
     **/

    class textylehubController extends ModuleObject {

        function init() {
            $oTextyleHubModel = &getModel('textylehub');
            $this->module_info = $oTextyleHubModel->getTextyleHubInfo();
            Context::set('module_info', $this->module_info);
        }

        function procTextylehubCreate() {
            $oModuleModel = &getModel('module');
            $oTextyleAdminController = &getAdminController('textyle');

            $logged_info = Context::get('logged_info');
            if(!$logged_info) return new Object(-1,'msg_invalid_request');

            $domain = Context::get('textyle_address');
            $title = Context::get('blog_title');
            $description = Context::get('blog_description');
            $accept_agree = Context::get('accept_agree');

            if($accept_agree != 'Y') return new Object(-1,'about_textyle_agreement');

            if(!$domain) return new Object(-1,'alert_textyle_address_is_null');
            if(strlen($domain)<4 || strlen($domain)>12) return new Object(-1,'alert_textyle_address_is_wrong');
            if(!preg_match('/^([a-z])([a-z0-9]+)$/i',$domain)) return new Object(-1,'alert_textyle_address_format');

            if($this->module_info->access_type != 'vid') {
                $domain = $domain.'.'.$this->module_info->default_domain;
            } else {
                if($oModuleModel->isIDExists($domain)) return new Object(-1,'alert_textyle_address_is_exists');
            }

            if(!$title) return new ObjecT(-1,'alert_textyle_title_is_null');

            if($logged_info->is_admin != 'Y') {
                $args->member_srl = $logged_info->member_srl;
                $output = executeQueryArray('textylehub.getOwnTextyle', $args);
                $own_textyle_count = count($output->data);
                if(!$this->grant->create || $this->module_info->textyle_creation_count<=$own_textyle_count) return new Object(-1,'alert_disable_to_create');
            }

            // textyle 생성
            $output = $oTextyleAdminController->insertTextyle($domain, $logged_info->user_id);
            if(!$output->toBool()) return $output;

            $module_srl = $output->get('module_srl');

            // 생성된 텍스타일의 제목/ 소개 변경
            $targs->textyle_title = $title;
            $targs->textyle_content = $description;
            $targs->module_srl = $module_srl;

            $this->setRedirectUrl(getSiteUrl($domain));
        }

    }
?>
