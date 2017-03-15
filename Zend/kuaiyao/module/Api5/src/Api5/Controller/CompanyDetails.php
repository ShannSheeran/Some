<?php
namespace Api5\Controller;

use Api5\Controller\Request\CompanyDetailsRequest;

/**
 * 名片详情
 *
 * @author
 *
 */
class CompanyDetails extends CommonController
{

    function __construct()
    {
        $this->myRequest = new CompanyDetailsRequest();
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
        
        $where = array();
        $id = (int) $request->id; // 请求得到
                                  // 公司id
        
        if ($id) {
            $company_info = $this->getCompanyTable()->getOne(array(
                'id' => $id
            ));
            
            if (! $company_info) {
                return STATUS_NODATA;
            }
            
            $image_id = $this->getImageTable()->getOne(array(
                'id' => $company_info['image']
            ));
            $image_li_id = $this->getImageTable()->getOne(array(
                'id' => $company_info['license_image']
            ));
            $image_faren_id = $this->getImageTable()->getOne(array(
                'id' => $company_info['ID_image']
            ));
            $licenseImage = array(
                'id' => $company_info['license_image'] ? $company_info['license_image'] : 0,
                'path' => $image_li_id['path'] . $image_li_id['filename'] ? $image_li_id['path'] . $image_li_id['filename'] : ""
            );
            $idImage = array(
                'id' => $company_info['ID_image'] ? $company_info['ID_image'] : 0,
                'path' => $image_faren_id['path'] . $image_faren_id['filename'] ? $image_faren_id['path'] . $image_faren_id['filename'] : ""
            );
            $address = array(
                'regionId' => $company_info['region_id'] ? $company_info['region_id'] : '0',
                'regionInfo' => (array) json_decode($company_info['region_info']),
                'street' => $company_info['street'],
                'latitude' => $company_info['latitude'],
                'longitude' => $company_info['longitude']
            );
            
            $user = array(
                'id' => $company_info['user_id']
            );
            
            $pr_data = $this->tags_data(1, $company_info->id);
            if ($pr_data) {
                $tag = array();
                foreach ($pr_data as $val) {
                    $tag[]['tag'] = array(
                        'name' => $val
                    );
                }
                $provideTags = $tag;
            } else {
                $provideTags = array();
            }
            $needs_data = $this->tags_data(2, $company_info->id);
            
            if ($needs_data) {
                $tag = array();
                foreach ($needs_data as $val) {
                    $tag[]['tag'] = array(
                        'name' => $val
                    );
                }
                $needsTags = $tag;
            } else {
                $needsTags = array();
            }
            $details = array(
                'id' => $company_info['id'],
                'name' => $company_info['name'],
                'imageId' => $company_info['image'],
                'imagePath' => $image_id['path'] . $image_id['filename'],
                'address' => $address,
                'categoryId' => $company_info['category_id'],
                'scale' => $company_info['scale'],
                'home' => $company_info['home'],
                'telephone' => $company_info['telephone'],
                'description' => strip_tags($company_info['description']),
                'project' => strip_tags($company_info['project']),
                'statStaff' => $company_info['stat_stuff'],
                'statAudit' => $company_info['stat_audit'],
                'isProvide' => $company_info['provide'],
                'provideTags' => $provideTags,
                'isNeed' => $company_info['needs'],
                'needsTags' => $needsTags,
                'auditStatus' => $company_info['audit_status'],
                'licenseImage' => $licenseImage,
                'idImage' => $idImage,
                'user' => $user
            );
        } else {
            return STATUS_NODATA;
        }
        
        $response->company = $details;
        return $response;
    }

    public function tags_data($type, $id)
    {
        
        // 查询标签表数据
        $where['foreign_id'] = $id;
        $where['type'] = $type;
        
        $tage_re_data = $this->getTagsRelationsTable()->fetchAll($where);
        if (! $tage_re_data) {
            return "";
        }
        
        $tags_need = array();
        foreach ($tage_re_data as $val) {
            $tags_need[] = $val['tag_id'];
        }
        
        $name_need = array();
        $tags_need_data = $this->getTagsTable()->fetchAll(array(
            'id' => $tags_need
        ));
        foreach ($tags_need_data as $value) {
            $name_need[] = $value['name'];
        }
        return $name_need;
    }
}