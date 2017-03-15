<?php
namespace Api5\Controller;

use Api5\Controller\Request\CompanySwitchRequest;

/**
 * 名片详情
 *
 * @author
 *
 */
class CompanySwitch extends CommonController
{

    function __construct()
    {
        $this->myRequest = new CompanySwitchRequest();
        parent::__construct();
    }

    /**
     *
     * @return string|\Api21\Controller\Common\Response
     */
    public function index()
    {
        $request = $this->getAiiRequest(); // 获取请求参数
        $response = $this->getAiiResponse(); // 初始化返回参数
        $action = $request->action;
        $open = $request->open;
        
        if (isset($action) && $action == 2) {
            
            
            $where = array(
                'company_id' => $request->companyId,
                'id' => $request->cardId
            );
            $company_status = $this->getViewPageCarteTable()->getOne($where);
            $company_id = $this->getCompanyTable()->getOne(array(
                'id' => $request->companyId
            ));
            if ($company_status) {
                $data = array(
                    'company' => '',
                    'company_id' => '',
                    'company_status' => 1
                );
              /*   $this->getViewPageCarteTable()->updateData($data, array(
                    'id' => $request->cardId
                )); */
                $this->getCarteTable()->updateData($data, array(
                    'id' => $company_status['c_id']
                ));
                if ($company_id['stat_stuff'] > 0) {
                    $stat_stuff = $company_id['stat_stuff'] - 1;
                } else {
                    $stat_stuff = 0;
                }
                $stat_data = array(
                    'stat_stuff' => $stat_stuff
                );
                $this->getCompanyTable()->updateData($stat_data, array(
                    'id' => $request->companyId
                ));
            } else {
                return STATUS_NODATA;
            }
        }
        
        if (isset($action) && $action == 1) {
            $array = array(
                1,
                2
            );
            if (! in_array($open, $array)) {
                return STATUS_UNKNOWN;
            }
            $company_id = $this->getCompanyTable()->getOne(array(
                'id' => $request->companyId
            )); 
            $user_id = $this->getUserId();
            if ($user_id == $company_id['user_id']) {
                $where = array(
                    'company_id' => $request->companyId,
                    'id' => $request->cardId
                );
                $company_status = $this->getViewPageCarteTable()->getOne($where);
                if ($company_status) {
                    if ($request->open == 1) {
                        $data = array(
                            'company_status' => 3
                        );
                        $this->getCarteTable()->updateData($data, array(
                            'id' => $company_status['c_id']
                        ));
                        /* $this->getViewPageCarteTable()->updateData($data, array( 'company_id' => $request->companyId,'id' => $request->cardId,)); */
                        $stat_stuff = $company_id['stat_stuff'] + 1;
                        if ($company_id['stat_audit'] > 0) {
                            $stat_audit = $company_id['stat_audit'] - 1;
                        } else {
                            $stat_audit = 0;
                        }
                        $data = array(
                            'stat_stuff' => $stat_stuff,
                            'stat_audit' => $stat_audit
                        );
                        $this->getCompanyTable()->updateData($data, array(
                            'id' => $request->companyId
                        ));
                    } else {
                        $data = array(
                            'company_status' => 4
                        );
                        $this->getCarteTable()->updateData($data, array(
                            'id' => $company_status['c_id']
                        ));
                        /*$this->getViewPageCarteTable()->updateData($data,array('company_id'=>$request->companyId,'id'=>$request->cardId,));*/
                        if ($company_id['stat_audit'] > 0) {
                            $stat_audit = $company_id['stat_audit'] - 1;
                        } else {
                            $stat_audit = 0;
                        }
                        $data = array(
                            'stat_audit' => $stat_audit
                        );
                        $this->getCompanyTable()->updateData($data, array(
                            'id' => $request->companyId
                        ));
                    }
                } else {
                    return STATUS_COMPANY_NOTEXIST;
                }
            } else {
                return DESCRIPTION_1115;
            }
        }
    }
}