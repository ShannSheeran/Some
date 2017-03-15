<?php
namespace Admin\Controller;
        
use Zend\Db\Adapter\Adapter;
use Zend\Mvc\Controller\AbstractActionController;
use Admin\Model\AdminTable;use Admin\Model\AdminCategoryTable;use Admin\Model\AdsTable;use Admin\Model\AdsPositionTable;use Admin\Model\ArticleTable;use Admin\Model\ArticleCategoryTable;use Admin\Model\CartTable;use Admin\Model\ContactsTable;use Admin\Model\CustomerServiceApplyTable;use Admin\Model\DataStatisticsTable;use Admin\Model\DeviceUserTable;use Admin\Model\ExpressListTable;use Admin\Model\FeedbackTable;use Admin\Model\FinancialTable;use Admin\Model\GoodsTable;use Admin\Model\GoodsCategoryTable;use Admin\Model\GoodsSpecificationTable;use Admin\Model\GoodsTrackingTable;use Admin\Model\ImageTable;use Admin\Model\JobTable;use Admin\Model\LeaveMessageTable;use Admin\Model\LoginTable;use Admin\Model\ModuleTable;use Admin\Model\NotificationTable;use Admin\Model\NotificationRecordsTable;use Admin\Model\OrderTable;use Admin\Model\OrderGoodsTable;use Admin\Model\OrderTrackingTable;use Admin\Model\RegionTable;use Admin\Model\SetupTable;use Admin\Model\SmsCodeTable;use Admin\Model\UserTable;use Admin\Model\WeekPlanTable;
        
class Table extends AbstractActionController
{
        
    public $adapter;
protected $AdminTable;protected $AdminCategoryTable;protected $AdsTable;protected $AdsPositionTable;protected $ArticleTable;protected $ArticleCategoryTable;protected $CartTable;protected $ContactsTable;protected $CustomerServiceApplyTable;protected $DataStatisticsTable;protected $DeviceUserTable;protected $ExpressListTable;protected $FeedbackTable;protected $FinancialTable;protected $GoodsTable;protected $GoodsCategoryTable;protected $GoodsSpecificationTable;protected $GoodsTrackingTable;protected $ImageTable;protected $JobTable;protected $LeaveMessageTable;protected $LoginTable;protected $ModuleTable;protected $NotificationTable;protected $NotificationRecordsTable;protected $OrderTable;protected $OrderGoodsTable;protected $OrderTrackingTable;protected $RegionTable;protected $SetupTable;protected $SmsCodeTable;protected $UserTable;protected $WeekPlanTable;
    public function __construct()
    {
        $driver = array(
            "driver" => "Pdo",
            "dsn" => "mysql:dbname=" . DB_NAME . ";host=" . DB_HOST,
            "username" => DB_USER,
            "password" => DB_PASSWORD,
            "charset" => DB_CHARSET,
            "driver_options" => array(
                "1002" => "SET NAMES '".DB_SET_NAME."'"
            )
        );
        $adapter = new Adapter($driver);
        $this->adapter = $adapter;
    }protected function getAdminTable()
    {
        if (! $this->AdminTable)
        {
            $this->AdminTable = new AdminTable($this->adapter);
        }
        return $this->AdminTable;
    }protected function getAdminCategoryTable()
    {
        if (! $this->AdminCategoryTable)
        {
            $this->AdminCategoryTable = new AdminCategoryTable($this->adapter);
        }
        return $this->AdminCategoryTable;
    }protected function getAdsTable()
    {
        if (! $this->AdsTable)
        {
            $this->AdsTable = new AdsTable($this->adapter);
        }
        return $this->AdsTable;
    }protected function getAdsPositionTable()
    {
        if (! $this->AdsPositionTable)
        {
            $this->AdsPositionTable = new AdsPositionTable($this->adapter);
        }
        return $this->AdsPositionTable;
    }protected function getArticleTable()
    {
        if (! $this->ArticleTable)
        {
            $this->ArticleTable = new ArticleTable($this->adapter);
        }
        return $this->ArticleTable;
    }protected function getArticleCategoryTable()
    {
        if (! $this->ArticleCategoryTable)
        {
            $this->ArticleCategoryTable = new ArticleCategoryTable($this->adapter);
        }
        return $this->ArticleCategoryTable;
    }protected function getCartTable()
    {
        if (! $this->CartTable)
        {
            $this->CartTable = new CartTable($this->adapter);
        }
        return $this->CartTable;
    }protected function getContactsTable()
    {
        if (! $this->ContactsTable)
        {
            $this->ContactsTable = new ContactsTable($this->adapter);
        }
        return $this->ContactsTable;
    }protected function getCustomerServiceApplyTable()
    {
        if (! $this->CustomerServiceApplyTable)
        {
            $this->CustomerServiceApplyTable = new CustomerServiceApplyTable($this->adapter);
        }
        return $this->CustomerServiceApplyTable;
    }protected function getDataStatisticsTable()
    {
        if (! $this->DataStatisticsTable)
        {
            $this->DataStatisticsTable = new DataStatisticsTable($this->adapter);
        }
        return $this->DataStatisticsTable;
    }protected function getDeviceUserTable()
    {
        if (! $this->DeviceUserTable)
        {
            $this->DeviceUserTable = new DeviceUserTable($this->adapter);
        }
        return $this->DeviceUserTable;
    }protected function getExpressListTable()
    {
        if (! $this->ExpressListTable)
        {
            $this->ExpressListTable = new ExpressListTable($this->adapter);
        }
        return $this->ExpressListTable;
    }protected function getFeedbackTable()
    {
        if (! $this->FeedbackTable)
        {
            $this->FeedbackTable = new FeedbackTable($this->adapter);
        }
        return $this->FeedbackTable;
    }protected function getFinancialTable()
    {
        if (! $this->FinancialTable)
        {
            $this->FinancialTable = new FinancialTable($this->adapter);
        }
        return $this->FinancialTable;
    }protected function getGoodsTable()
    {
        if (! $this->GoodsTable)
        {
            $this->GoodsTable = new GoodsTable($this->adapter);
        }
        return $this->GoodsTable;
    }protected function getGoodsCategoryTable()
    {
        if (! $this->GoodsCategoryTable)
        {
            $this->GoodsCategoryTable = new GoodsCategoryTable($this->adapter);
        }
        return $this->GoodsCategoryTable;
    }protected function getGoodsSpecificationTable()
    {
        if (! $this->GoodsSpecificationTable)
        {
            $this->GoodsSpecificationTable = new GoodsSpecificationTable($this->adapter);
        }
        return $this->GoodsSpecificationTable;
    }protected function getGoodsTrackingTable()
    {
        if (! $this->GoodsTrackingTable)
        {
            $this->GoodsTrackingTable = new GoodsTrackingTable($this->adapter);
        }
        return $this->GoodsTrackingTable;
    }protected function getImageTable()
    {
        if (! $this->ImageTable)
        {
            $this->ImageTable = new ImageTable($this->adapter);
        }
        return $this->ImageTable;
    }protected function getJobTable()
    {
        if (! $this->JobTable)
        {
            $this->JobTable = new JobTable($this->adapter);
        }
        return $this->JobTable;
    }protected function getLeaveMessageTable()
    {
        if (! $this->LeaveMessageTable)
        {
            $this->LeaveMessageTable = new LeaveMessageTable($this->adapter);
        }
        return $this->LeaveMessageTable;
    }protected function getLoginTable()
    {
        if (! $this->LoginTable)
        {
            $this->LoginTable = new LoginTable($this->adapter);
        }
        return $this->LoginTable;
    }protected function getModuleTable()
    {
        if (! $this->ModuleTable)
        {
            $this->ModuleTable = new ModuleTable($this->adapter);
        }
        return $this->ModuleTable;
    }protected function getNotificationTable()
    {
        if (! $this->NotificationTable)
        {
            $this->NotificationTable = new NotificationTable($this->adapter);
        }
        return $this->NotificationTable;
    }protected function getNotificationRecordsTable()
    {
        if (! $this->NotificationRecordsTable)
        {
            $this->NotificationRecordsTable = new NotificationRecordsTable($this->adapter);
        }
        return $this->NotificationRecordsTable;
    }protected function getOrderTable()
    {
        if (! $this->OrderTable)
        {
            $this->OrderTable = new OrderTable($this->adapter);
        }
        return $this->OrderTable;
    }protected function getOrderGoodsTable()
    {
        if (! $this->OrderGoodsTable)
        {
            $this->OrderGoodsTable = new OrderGoodsTable($this->adapter);
        }
        return $this->OrderGoodsTable;
    }protected function getOrderTrackingTable()
    {
        if (! $this->OrderTrackingTable)
        {
            $this->OrderTrackingTable = new OrderTrackingTable($this->adapter);
        }
        return $this->OrderTrackingTable;
    }protected function getRegionTable()
    {
        if (! $this->RegionTable)
        {
            $this->RegionTable = new RegionTable($this->adapter);
        }
        return $this->RegionTable;
    }protected function getSetupTable()
    {
        if (! $this->SetupTable)
        {
            $this->SetupTable = new SetupTable($this->adapter);
        }
        return $this->SetupTable;
    }protected function getSmsCodeTable()
    {
        if (! $this->SmsCodeTable)
        {
            $this->SmsCodeTable = new SmsCodeTable($this->adapter);
        }
        return $this->SmsCodeTable;
    }protected function getUserTable()
    {
        if (! $this->UserTable)
        {
            $this->UserTable = new UserTable($this->adapter);
        }
        return $this->UserTable;
    }protected function getWeekPlanTable()
    {
        if (! $this->WeekPlanTable)
        {
            $this->WeekPlanTable = new WeekPlanTable($this->adapter);
        }
        return $this->WeekPlanTable;
    }
}