<?php
namespace Admin\Controller;

use Zend\Db\Adapter\Adapter;
use Zend\Mvc\Controller\AbstractActionController;
use Admin\Model\AdminTable;
use Admin\Model\AdminCategoryTable;
use Admin\Model\AdsTable;
use Admin\Model\AdsPositionTable;
use Admin\Model\ArticleTable;
use Admin\Model\ArticleCategoryTable;
use Admin\Model\CartTable;
use Admin\Model\ContactsTable;
use Admin\Model\CustomerServiceApplyTable;
use Admin\Model\ViewCustomerServiceApplyTable;
use Admin\Model\DataStatisticsTable;
use Admin\Model\DeviceUserTable;
use Admin\Model\ExpressListTable;
use Admin\Model\FeedbackTable;
use Admin\Model\FinancialTable;
use Admin\Model\GoodsTable;
use Admin\Model\GoodsCategoryTable;
use Admin\Model\GoodsSpecificationTable;
use Admin\Model\GoodsTrackingTable;
use Admin\Model\ImageTable;
use Admin\Model\JobTable;
use Admin\Model\LeaveMessageTable;
use Admin\Model\LoginTable;
use Admin\Model\ModuleTable;
use Admin\Model\NotificationTable;
use Admin\Model\NotificationRecordsTable;
use Admin\Model\OrderTable;
use Admin\Model\OrderGoodsTable;
use Admin\Model\OrderTrackingTable;
use Admin\Model\RegionTable;
use Admin\Model\SetupTable;
use Admin\Model\SmsCodeTable;
use Admin\Model\UserTable;
use Admin\Model\WeekPlanTable;
use Admin\Model\UserLabelTable;
use Admin\Model\ViewUserTable;
use Admin\Model\GoodsUnitTable;
use Admin\Model\ViewAdsTable;
use Admin\Model\ViewGoodsCategoryTable;
use Admin\Model\ViewGoodsTable;
use Admin\Model\AccountListTable;
use Admin\Model\BankListTable;
use Admin\Model\ViewOrderTable;
use Admin\Model\ViewOrderGoodsTable;
use Admin\Model\ViewFinancialTable;
use Admin\Model\GoodsStatisticsTable;
use Admin\Model\ViewGoodsStatisticsTable;
use Admin\Model\ViewCartTable;
use Admin\Model\DepartmentTable;
use Admin\Model\StaffTable;
use Admin\Model\ViewStaffTable;
use Admin\Model\ProductionTable;
use Admin\Model\ViewMessageTable;
use Admin\Model\ViewFeedbackTable;
use Admin\Model\MessageTable;
use Admin\Model\ViewGoodsTrackingTable;
use Admin\Model\TimeNodeTable;
use Admin\Model\ViewWeekPlanTable;

class TableController extends AbstractActionController
{

    public $adapter;

    protected $AdminTable;

    protected $AdminCategoryTable;

    protected $AdsTable;

    protected $AdsPositionTable;

    protected $ArticleTable;

    protected $ArticleCategoryTable;

    protected $CartTable;

    protected $ContactsTable;

    protected $CustomerServiceApplyTable;

    protected $DataStatisticsTable;

    protected $DeviceUserTable;

    protected $ExpressListTable;

    protected $FeedbackTable;

    protected $FinancialTable;

    protected $GoodsTable;

    protected $GoodsCategoryTable;

    protected $GoodsSpecificationTable;

    protected $GoodsTrackingTable;

    protected $ImageTable;

    protected $JobTable;

    protected $LeaveMessageTable;

    protected $LoginTable;

    protected $ModuleTable;

    protected $NotificationTable;

    protected $NotificationRecordsTable;

    protected $OrderTable;

    protected $OrderGoodsTable;

    protected $OrderTrackingTable;

    protected $RegionTable;

    protected $SetupTable;

    protected $SmsCodeTable;

    protected $UserTable;

    protected $WeekPlanTable;
    
    protected $UserLabelTable;
    
    protected $ViewUserTable;
    
    protected $GoodsUnitTable;
    
    protected $ViewAdsTable;
    
    protected $ViewGoodsCategoryTable;
    
    protected $ViewGoodsTable;
    
    protected $AccountListTable;
    
    protected $BankListTable;
    
    protected $ViewOrderTable;
    
    protected $ViewOrderGoodsTable;
    
    protected $ViewFinancialTable;
    
    protected $GoodsStatisticsTable;
    
    protected $ViewGoodsStatisticsTable;
    
    protected $ViewCartTable;
    
    protected $DepartmentTable;
    
    protected $StaffTable;
    
    protected $ViewStaffTable;
    
    protected $ProductionTable;

    protected $ViewMessageTable;
    
    protected $MessageTable;

    protected $ViewFeedbackTable;
    
    protected $ViewCustomerServiceApplyTable;

    protected $ViewGoodsTrackingTable;
    
    protected $TimeNodeTable;
    
    protected $ViewWeekPlanTable;
    
    public function __construct()
    {
        $driver = array(
            "driver" => "Pdo",
            "dsn" => "mysql:dbname=" . DB_NAME . ";host=" . DB_HOST,
            "username" => DB_USER,
            "password" => DB_PASSWORD,
            "charset" => DB_CHARSET,
            "driver_options" => array(
                "1002" => "SET NAMES '" . DB_SET_NAME . "'"
            )
        );
        $adapter = new Adapter($driver);
        $this->adapter = $adapter;
    }

    protected function getAdminTable()
    {
        if (! $this->AdminTable) {
            $this->AdminTable = new AdminTable($this->adapter);
        }
        return $this->AdminTable;
    }

    protected function getAdminCategoryTable()
    {
        if (! $this->AdminCategoryTable) {
            $this->AdminCategoryTable = new AdminCategoryTable($this->adapter);
        }
        return $this->AdminCategoryTable;
    }

    protected function getAdsTable()
    {
        if (! $this->AdsTable) {
            $this->AdsTable = new AdsTable($this->adapter);
        }
        return $this->AdsTable;
    }

    protected function getAdsPositionTable()
    {
        if (! $this->AdsPositionTable) {
            $this->AdsPositionTable = new AdsPositionTable($this->adapter);
        }
        return $this->AdsPositionTable;
    }

    protected function getArticleTable()
    {
        if (! $this->ArticleTable) {
            $this->ArticleTable = new ArticleTable($this->adapter);
        }
        return $this->ArticleTable;
    }

    protected function getArticleCategoryTable()
    {
        if (! $this->ArticleCategoryTable) {
            $this->ArticleCategoryTable = new ArticleCategoryTable($this->adapter);
        }
        return $this->ArticleCategoryTable;
    }

    protected function getCartTable()
    {
        if (! $this->CartTable) {
            $this->CartTable = new CartTable($this->adapter);
        }
        return $this->CartTable;
    }

    protected function getContactsTable()
    {
        if (! $this->ContactsTable) {
            $this->ContactsTable = new ContactsTable($this->adapter);
        }
        return $this->ContactsTable;
    }

    protected function getCustomerServiceApplyTable()
    {
        if (! $this->CustomerServiceApplyTable) {
            $this->CustomerServiceApplyTable = new CustomerServiceApplyTable($this->adapter);
        }
        return $this->CustomerServiceApplyTable;
    }

    protected function getDataStatisticsTable()
    {
        if (! $this->DataStatisticsTable) {
            $this->DataStatisticsTable = new DataStatisticsTable($this->adapter);
        }
        return $this->DataStatisticsTable;
    }

    protected function getDeviceUserTable()
    {
        if (! $this->DeviceUserTable) {
            $this->DeviceUserTable = new DeviceUserTable($this->adapter);
        }
        return $this->DeviceUserTable;
    }

    protected function getExpressListTable()
    {
        if (! $this->ExpressListTable) {
            $this->ExpressListTable = new ExpressListTable($this->adapter);
        }
        return $this->ExpressListTable;
    }

    protected function getFeedbackTable()
    {
        if (! $this->FeedbackTable) {
            $this->FeedbackTable = new FeedbackTable($this->adapter);
        }
        return $this->FeedbackTable;
    }

    protected function getFinancialTable()
    {
        if (! $this->FinancialTable) {
            $this->FinancialTable = new FinancialTable($this->adapter);
        }
        return $this->FinancialTable;
    }

    protected function getGoodsTable()
    {
        if (! $this->GoodsTable) {
            $this->GoodsTable = new GoodsTable($this->adapter);
        }
        return $this->GoodsTable;
    }

    protected function getGoodsCategoryTable()
    {
        if (! $this->GoodsCategoryTable) {
            $this->GoodsCategoryTable = new GoodsCategoryTable($this->adapter);
        }
        return $this->GoodsCategoryTable;
    }

    protected function getGoodsSpecificationTable()
    {
        if (! $this->GoodsSpecificationTable) {
            $this->GoodsSpecificationTable = new GoodsSpecificationTable($this->adapter);
        }
        return $this->GoodsSpecificationTable;
    }

    protected function getGoodsTrackingTable()
    {
        if (! $this->GoodsTrackingTable) {
            $this->GoodsTrackingTable = new GoodsTrackingTable($this->adapter);
        }
        return $this->GoodsTrackingTable;
    }
    
    protected function getGoodsUnitTable()
    {
        if(! $this->GoodsUnitTable)
        {
            $this->GoodsUnitTable = new GoodsUnitTable($this->adapter);
        }
        return $this->GoodsUnitTable;
    }

    protected function getImageTable()
    {
        if (! $this->ImageTable) {
            $this->ImageTable = new ImageTable($this->adapter);
        }
        return $this->ImageTable;
    }

    protected function getJobTable()
    {
        if (! $this->JobTable) {
            $this->JobTable = new JobTable($this->adapter);
        }
        return $this->JobTable;
    }

    protected function getLeaveMessageTable()
    {
        if (! $this->LeaveMessageTable) {
            $this->LeaveMessageTable = new LeaveMessageTable($this->adapter);
        }
        return $this->LeaveMessageTable;
    }

    protected function getLoginTable()
    {
        if (! $this->LoginTable) {
            $this->LoginTable = new LoginTable($this->adapter);
        }
        return $this->LoginTable;
    }

    protected function getModuleTable()
    {
        if (! $this->ModuleTable) {
            $this->ModuleTable = new ModuleTable($this->adapter);
        }
        return $this->ModuleTable;
    }

    protected function getNotificationTable()
    {
        if (! $this->NotificationTable) {
            $this->NotificationTable = new NotificationTable($this->adapter);
        }
        return $this->NotificationTable;
    }

    protected function getNotificationRecordsTable()
    {
        if (! $this->NotificationRecordsTable) {
            $this->NotificationRecordsTable = new NotificationRecordsTable($this->adapter);
        }
        return $this->NotificationRecordsTable;
    }

    protected function getOrderTable()
    {
        if (! $this->OrderTable) {
            $this->OrderTable = new OrderTable($this->adapter);
        }
        return $this->OrderTable;
    }

    protected function getOrderGoodsTable()
    {
        if (! $this->OrderGoodsTable) {
            $this->OrderGoodsTable = new OrderGoodsTable($this->adapter);
        }
        return $this->OrderGoodsTable;
    }

    protected function getOrderTrackingTable()
    {
        if (! $this->OrderTrackingTable) {
            $this->OrderTrackingTable = new OrderTrackingTable($this->adapter);
        }
        return $this->OrderTrackingTable;
    }
    protected function getUserTable()
    {
        if (! $this->UserTable)
        {
            $this->UserTable = new UserTable($this->adapter);
        }
        return $this->UserTable;
    }
    protected function getUserLabelTable()
    {
        if (! $this->UserLabelTable)
        {
            $this->UserLabelTable = new UserLabelTable($this->adapter);
        }
        return $this->UserLabelTable;
    }
    protected function getViewUserTable()
    {
        if (! $this->ViewUserTable)
        {
            $this->ViewUserTable = new ViewUserTable($this->adapter);
        }
        return $this->ViewUserTable;
    }
    protected function getRegionTable()
    {
        if (! $this->RegionTable)
        {
            $this->RegionTable = new RegionTable($this->adapter);
        }
        return $this->RegionTable;
    }

    protected function getSetupTable()
    {
        if (! $this->SetupTable) {
            $this->SetupTable = new SetupTable($this->adapter);
        }
        return $this->SetupTable;
    }

    protected function getSmsCodeTable()
    {
        if (! $this->SmsCodeTable) {
            $this->SmsCodeTable = new SmsCodeTable($this->adapter);
        }
        return $this->SmsCodeTable;
    }

    protected function getWeekPlanTable()
    {
        if (! $this->WeekPlanTable) {
            $this->WeekPlanTable = new WeekPlanTable($this->adapter);
        }
        return $this->WeekPlanTable;
    }
    
    protected function getViewAdsTable()
    {
        if (! $this->ViewAdsTable) {
            $this->ViewAdsTable = new ViewAdsTable($this->adapter);
        }
        return $this->ViewAdsTable;
    }
    
    protected function getViewGoodsCategoryTable()
    {
        if (! $this->ViewGoodsCategoryTable) {
            $this->ViewGoodsCategoryTable = new ViewGoodsCategoryTable($this->adapter);
        }
        return $this->ViewGoodsCategoryTable;
    }
    
    protected function getViewGoodsTable()
    {
        if (! $this->ViewGoodsTable) {
            $this->ViewGoodsTable = new ViewGoodsTable($this->adapter);
        }
        return $this->ViewGoodsTable;
    }
    
    protected function getAccountListTable()
    {
        if (! $this->AccountListTable) {
            $this->AccountListTable = new AccountListTable($this->adapter);
        }
        return $this->AccountListTable;
    }
    
    protected function getBankListTable()
    {
        if (! $this->BankListTable) {
            $this->BankListTable = new BankListTable($this->adapter);
        }
        return $this->BankListTable;
    }
    
    protected function getViewOrderTable()
    {
        if (! $this->ViewOrderTable) {
            $this->ViewOrderTable = new ViewOrderTable($this->adapter);
        }
        return $this->ViewOrderTable;
    }
    
    protected function getViewOrderGoodsTable()
    {
        if (! $this->ViewOrderGoodsTable) {
            $this->ViewOrderGoodsTable = new ViewOrderGoodsTable($this->adapter);
        }
        return $this->ViewOrderGoodsTable;
    }
    
    protected function getViewFinancialTable()
    {
        if (! $this->ViewFinancialTable) {
            $this->ViewFinancialTable = new ViewFinancialTable($this->adapter);
        }
        return $this->ViewFinancialTable;
    }
    
    protected function getGoodsStatisticsTable()
    {
        if (! $this->GoodsStatisticsTable) {
            $this->GoodsStatisticsTable = new GoodsStatisticsTable($this->adapter);
        }
        return $this->GoodsStatisticsTable;
    }
    
    protected function getViewCartTable()
    {
        if (! $this->ViewCartTable) {
            $this->ViewCartTable = new ViewCartTable($this->adapter);
        }
        return $this->ViewCartTable;
    }
    
    protected function getViewGoodsStatisticsTable()
    {
        if (! $this->ViewGoodsStatisticsTable) {
            $this->ViewGoodsStatisticsTable = new ViewGoodsStatisticsTable($this->adapter);
        }
        return $this->ViewGoodsStatisticsTable;
    }
    
    protected function getDepartmentTable()
    {
        if (! $this->DepartmentTable) {
            $this->DepartmentTable = new DepartmentTable($this->adapter);
        }
        return $this->DepartmentTable;
    }
    
    protected function getStaffTable()
    {
        if (! $this->StaffTable) {
            $this->StaffTable = new StaffTable($this->adapter);
        }
        return $this->StaffTable;
    }
    
    protected function getViewStaffTable()
    {
        if (! $this->ViewStaffTable) {
            $this->ViewStaffTable = new ViewStaffTable($this->adapter);
        }
        return $this->ViewStaffTable;
    }
    
    protected function getProductionTable()
    {
        if (! $this->ProductionTable) {
            $this->ProductionTable = new ProductionTable($this->adapter);
        }
        return $this->ProductionTable;
    }

        protected function getViewMessageTable()
    {
        if (! $this->ViewMessageTable) {
            $this->ViewMessageTable = new ViewMessageTable($this->adapter);
        }
        return $this->ViewMessageTable;
    }
    
    protected function getViewFeedbackTable()
    {
        if (! $this->ViewFeedbackTable) {
            $this->ViewFeedbackTable = new ViewFeedbackTable($this->adapter);
        }
        return $this->ViewFeedbackTable;
    }

    protected function getViewCustomerServiceApplyTable()
    {
        if (! $this->ViewCustomerServiceApplyTable) {
            $this->ViewCustomerServiceApplyTable = new ViewCustomerServiceApplyTable($this->adapter);
        }
        return $this->ViewCustomerServiceApplyTable;
    }
    
    protected function getViewGoodsTrackingTable()
    {
        if (! $this->ViewGoodsTrackingTable) {
            $this->ViewGoodsTrackingTable = new ViewGoodsTrackingTable($this->adapter);
        }
        return $this->ViewGoodsTrackingTable;
    }
    
    protected function getMessageTable()
    {
        if (! $this->MessageTable) {
            $this->MessageTable = new MessageTable($this->adapter);
        }
        return $this->MessageTable;
    }
    
    protected function getTimeNodeTable()
    {
        if (! $this->TimeNodeTable) {
            $this->TimeNodeTable = new TimeNodeTable($this->adapter);
        }
        return $this->TimeNodeTable;
    }
    
    protected function getViewWeekPlanTable()
    {
        if (! $this->ViewWeekPlanTable) {
            $this->ViewWeekPlanTable = new ViewWeekPlanTable($this->adapter);
        }
        return $this->ViewWeekPlanTable;
    }
}