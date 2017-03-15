<?php
namespace Admin\Controller;

use Zend\Db\Adapter\Adapter;
use Zend\Mvc\Controller\AbstractActionController;
use Admin\Model\AdminTable;
use Admin\Model\AdsMaterialTable;
use Admin\Model\AdsPositionTable;
use Admin\Model\AdsRelationTable;
use Admin\Model\AdsTargetTable;
use Admin\Model\CarteTable;
use Admin\Model\ChatTable;
use Admin\Model\ChatCommentTable;
use Admin\Model\ChatPraiseTable;
use Admin\Model\DeviceTable;
use Admin\Model\DeviceUserTable;
use Admin\Model\ImageTable;
use Admin\Model\InvitationCodeTable;
use Admin\Model\LoginTable;
use Admin\Model\NotificationTable;
use Admin\Model\NotificationRecordsTable;
use Admin\Model\PageTable;
use Admin\Model\RegionTable;
use Admin\Model\SmsCodeTable;
use Admin\Model\StatisticsTable;
use Admin\Model\SetupTable;
use Admin\Model\UserTable;
use Admin\Model\UserRelationTable;
use Admin\Model\ViewUserRelationTable;
use Admin\Model\ViewUserPageTable;
use Admin\Model\ViewInvitationCodeTable;
use Admin\Model\ViewPageViewTable;
use Admin\Model\ViewChatCommentTable;
use Admin\Model\ViewChatPraiseTable;
use Admin\Model\ViewPageCarteTable;
use Admin\Model\UserApplicationTable;
use Admin\Model\ViewUserOneRelationTable;
use Admin\Model\FinancialTable;
use Admin\Model\OrderTable;
use Admin\Model\ViewOrderTable;
use Admin\Model\ArticleTable;
use Admin\Model\NewsTable;
use Admin\Model\ViewFinancialTable;
use Admin\Model\PageViewRecordTable;
use Admin\Model\ArticleCategoryTable;
use Admin\Model\UserPartnerTable;
use Admin\Model\ViewMyPageTable;
use Admin\Model\CompanyTable;
use Admin\Model\AdsTable;
use Admin\Model\TagsTable;
use Admin\Model\ActivityTable;
use Admin\Model\ViewActivityCompanyTable;
use Admin\Model\ViewActivityTable;
use Admin\Model\UserAddressTable;
use Admin\Model\CodeUseTable;
use Admin\Model\StatisticsDayTable;
use Admin\Model\TagsRelationsTable;


class TableController extends AbstractActionController
{

    public $adapter;

    protected $AdminTable;
    
    protected $AdsMaterialTable;
    
    protected $AdsPositionTable;
    
    protected $AdsRelationTable;
    
    protected $AdsTargetTable;
    
    protected $CarteTable;
    
    protected $ChatTable;
    
    protected $ChatCommentTable;
    
    protected $ChatPraiseTable;
    
    protected $DeviceTable;
    
    protected $DeviceUserTable;
    
    protected $FinancialTable;
    
    protected $ImageTable;
    
    protected $InvitationCodeTable;
    
    protected $LoginTable;
    
    protected $NotificationTable;
    
    protected $NotificationRecordsTable;
    
    protected $OrderTable;
    
    protected $PageTable;
    
    protected $RegionTable;
    
    protected $SmsCodeTable;
    
    protected $StatisticsTable;
    
	protected $CodeUseTable;
	
    protected $SetupTable;
    
    protected $UserAddressTable;
    
    protected $UserTable;
    
    protected $UserRelationTable;
    
    protected $UserPartnerTable;
    
    protected $ViewUserRelationTable;
    
    protected $ViewUserPageTable;
    
    protected $PageViewRecordTable;
    
    protected $ViewPageViewTable;

    protected $ViewInvitationCodeTable;
    
    protected $ViewOrderTable;
    
    protected $ViewChatCommentTable;
    
    protected $ViewChatPraiseTable;
    
    protected $ViewFinancialTable;
    
    protected $ViewPageCarteTable;
    
    protected $UserApplicationTable;
    
    protected $ViewUserOneRelationTable;
    
    protected $ArticleTable;
    
    protected $ArticleCategoryTable;
    
    protected $CompanyTable;
    protected $AdsTable;
    protected $TagsTable;
    protected $TagsRelationsTable;
    protected $ActivityTable;
    protected $StatisticsDayTable;

    protected $NewsTable;
    protected $ProductTable;
    protected $ViewMyPageTable;
    protected $ViewActivityCompanyTable;
	protected $ViewActivityTable;
    public function __construct()
    {
        $driver = array(
            "driver" => "Pdo",
            "dsn" => "mysql:dbname=" . DB_NAME . ";host=" . DB_HOST,
            "username" => DB_USER,
            "password" => DB_PASSWORD,
            'charset' => DB_CHARSET,
            'driver_options' => array(
                "1002" => 'SET NAMES \''.DB_SET_NAME.'\''
            )
        );
        $adapter = new Adapter($driver);
        $this->adapter = $adapter;
    }
    
    protected function getAdminTable()
    {
        if (! $this->AdminTable)
        {
            $this->AdminTable = new AdminTable($this->adapter);
        }
        return $this->AdminTable;
    }
    
    protected function getAdsMaterialTable()
    {
    	if (! $this->AdsMaterialTable)
    	{
    		$this->AdsMaterialTable = new AdsMaterialTable($this->adapter);
    	}
    	return $this->AdsMaterialTable;
    }
    
    protected function getAdsPositionTable()
    {
    	if (! $this->AdsPositionTable)
    	{
    		$this->AdsPositionTable = new AdsPositionTable($this->adapter);
    	}
    	return $this->AdsPositionTable;
    }
    
    protected function getAdsRelationTable()
    {
    	if (! $this->AdsRelationTable)
    	{
    		$this->AdsRelationTable = new AdsRelationTable($this->adapter);
    	}
    	return $this->AdsRelationTable;
    }
    
    protected function getAdsTargetTable()
    {
    	if (! $this->AdsTargetTable)
    	{
    		$this->AdsTargetTable = new AdsTargetTable($this->adapter);
    	}
    	return $this->AdsTargetTable;
    }
    
    protected function getCarteTable()
    {
        if (! $this->CarteTable)
        {
            $this->CarteTable = new CarteTable($this->adapter);
        }
        return $this->CarteTable;
    }
    
    protected function getChatTable()
    {
    	if (! $this->ChatTable)
    	{
    		$this->ChatTable = new ChatTable($this->adapter);
    	}
    	return $this->ChatTable;
    }

    
    protected function getChatCommentTable()
    {
        if (! $this->ChatCommentTable)
        {
            $this->ChatCommentTable = new ChatCommentTable($this->adapter);
        }
        return $this->ChatCommentTable;
    }
    
    protected function getChatPraiseTable()
    {
        if (! $this->ChatPraiseTable)
        {
            $this->ChatPraiseTable = new ChatPraiseTable($this->adapter);
        }
        return $this->ChatPraiseTable;
    }
    
    protected function getDeviceTable()
    {
        if (! $this->DeviceTable)
        {
            $this->DeviceTable = new DeviceTable($this->adapter);
        }
        return $this->DeviceTable;
    }
    
    protected function getDeviceUserTable()
    {
        if (! $this->DeviceUserTable)
        {
            $this->DeviceUserTable = new DeviceUserTable($this->adapter);
        }
        return $this->DeviceUserTable;
    }
    
    protected function getFinancialTable()
    {
        if (! $this->FinancialTable)
        {
            $this->FinancialTable = new FinancialTable($this->adapter);
        }
        return $this->FinancialTable;
    }
    
    protected function getImageTable()
    {
        if (! $this->ImageTable)
        {
            $this->ImageTable = new ImageTable($this->adapter);
        }
        return $this->ImageTable;
    }
    
    protected function getInvitationCodeTable()
    {
        if (! $this->InvitationCodeTable)
        {
            $this->InvitationCodeTable = new InvitationCodeTable($this->adapter);
        }
        return $this->InvitationCodeTable;
    }
    
    protected function getLoginTable()
    {
        if (! $this->LoginTable)
        {
            $this->LoginTable = new LoginTable($this->adapter);
        }
        return $this->LoginTable;
    }
    
    protected function getNotificationTable()
    {
        if (! $this->NotificationTable)
        {
            $this->NotificationTable = new NotificationTable($this->adapter);
        }
        return $this->NotificationTable;
    }
    
    protected function getNotificationRecordsTable()
    {
        if (! $this->NotificationRecordsTable)
        {
            $this->NotificationRecordsTable = new NotificationRecordsTable($this->adapter);
        }
        return $this->NotificationRecordsTable;
    }
    
    protected function getOrderTable()
    {
        if (! $this->OrderTable)
        {
            $this->OrderTable = new OrderTable($this->adapter);
        }
        return $this->OrderTable;
    }
    
    protected function getPageTable()
    {
        if (! $this->PageTable)
        {
            $this->PageTable = new PageTable($this->adapter);
        }
        return $this->PageTable;
    }
    
    protected function getRegionTable()
    {
        if (! $this->RegionTable)
        {
            $this->RegionTable = new RegionTable($this->adapter);
        }
        return $this->RegionTable;
    }
    
    protected function getSmsCodeTable()
    {
        if (! $this->SmsCodeTable)
        {
            $this->SmsCodeTable = new SmsCodeTable($this->adapter);
        }
        return $this->SmsCodeTable;
    }
    
    protected function getStatisticsTable()
    {
        if (! $this->StatisticsTable)
        {
            $this->StatisticsTable = new StatisticsTable($this->adapter);
        }
        return $this->StatisticsTable;
    }
    
    protected function getSetupTable()
    {
    	if (! $this->SetupTable)
    	{
    		$this->SetupTable = new SetupTable($this->adapter);
    	}
    	return $this->SetupTable;
    }
    
    protected function getUserAddressTable()
    {
        if (! $this->UserAddressTable)
        {
            $this->UserAddressTable = new UserAddressTable($this->adapter);
        }
        return $this->UserAddressTable;
    }
    
    protected function getUserTable()
    {
        if (! $this->UserTable)
        {
            $this->UserTable = new UserTable($this->adapter);
        }
        return $this->UserTable;
    }
    
    protected function getUserRelationTable()
    {
        if (! $this->UserRelationTable)
        {
            $this->UserRelationTable = new UserRelationTable($this->adapter);
        }
        return $this->UserRelationTable;
    }
    
    protected function getUserPartnerTable()
    {
        if (! $this->UserPartnerTable)
        {
            $this->UserPartnerTable = new UserPartnerTable($this->adapter);
        }
        return $this->UserPartnerTable;
    }
    
    protected function getViewUserRelationTable()
    {
        if (! $this->ViewUserRelationTable)
        {
            $this->ViewUserRelationTable = new ViewUserRelationTable($this->adapter);
        }
        return $this->ViewUserRelationTable;
    }
    protected function getViewUserPageTable()
    {
    	if (! $this->ViewUserPageTable)
    	{
    		$this->ViewUserPageTable = new ViewUserPageTable($this->adapter);
    	}
    	return $this->ViewUserPageTable;
    }
    protected function getPageViewRecordTable()
    {
        if (! $this->PageViewRecordTable)
        {
            $this->PageViewRecordTable = new PageViewRecordTable($this->adapter);
        }
        return $this->PageViewRecordTable;
    }
    protected function getViewPageViewTable()
    {
        if (! $this->ViewPageViewTable)
        {
            $this->ViewPageViewTable = new ViewPageViewTable($this->adapter);
        }
        return $this->ViewPageViewTable;
    }

    protected function getViewInvitationCodeTable()
    {
    	if (! $this->ViewInvitationCodeTable)
    	{
    		$this->ViewInvitationCodeTable = new ViewInvitationCodeTable($this->adapter);
    	}
    	return $this->ViewInvitationCodeTable;
    }
    
    protected function getViewOrderTable()
    {
        if (! $this->ViewOrderTable)
        {
            $this->ViewOrderTable = new ViewOrderTable($this->adapter);
        }
        return $this->ViewOrderTable;
    }
    
    protected function getViewChatCommentTable()
    {
    	if (! $this->ViewChatCommentTable)
    	{
    		$this->ViewChatCommentTable = new ViewChatCommentTable($this->adapter);
    	}
    	return $this->ViewChatCommentTable;
    }
    
    protected function getViewChatPraiseTable()
    {
        if (! $this->ViewChatPraiseTable)
        {
            $this->ViewChatPraiseTable = new ViewChatPraiseTable($this->adapter);
        }
        return $this->ViewChatPraiseTable;
    }
    
    protected function getViewFinancialTable()
    {
        if (! $this->ViewFinancialTable)
        {
            $this->ViewFinancialTable = new ViewFinancialTable($this->adapter);
        }
        return $this->ViewFinancialTable;
    }
    
    protected function getViewPageCarteTable()
    {
        if (! $this->ViewPageCarteTable)
        {
            $this->ViewPageCarteTable = new ViewPageCarteTable($this->adapter);
        }
        return $this->ViewPageCarteTable;
    }
    protected function getUserApplicationTable()
    {
        if (! $this->UserApplicationTable)
        {
            $this->UserApplicationTable = new UserApplicationTable($this->adapter);
        }
        return $this->UserApplicationTable;
    }
    protected function getViewUserOneRelationTable()
    {
        if (! $this->ViewUserOneRelationTable)
        {
            $this->ViewUserOneRelationTable = new ViewUserOneRelationTable($this->adapter);
        }
        return $this->ViewUserOneRelationTable;
    }
    
    protected function getArticleCategoryTable()
    {
        if (! $this->ArticleCategoryTable)
        {
            $this->ArticleCategoryTable = new ArticleCategoryTable($this->adapter);
        }
        return $this->ArticleCategoryTable;
    }
    
    protected function getArticleTable()
    {
        if (! $this->ArticleTable)
        {
            $this->ArticleTable = new ArticleTable($this->adapter);
        }
        return $this->ArticleTable;
    }
	//CodeUseTable
	protected function getCodeUseTable()
    {
        if (! $this->CodeUseTable)
        {
            $this->CodeUseTable = new CodeUseTable($this->adapter);
        }
        return $this->CodeUseTable;
    }
	
    protected function getNewsTable()
    {
        if (! $this->NewsTable)
        {
            $this->NewsTable = new NewsTable($this->adapter);
        }
        return $this->NewsTable;
    }
    protected function getViewMyPageTable()
    {
        if (! $this->ViewMyPageTable)
        {
            $this->ViewMyPageTable = new ViewMyPageTable($this->adapter);
        }
        return $this->ViewMyPageTable;
    }
   protected function getProductTable()
    {
        if (! $this->ProductTable)
        {
            $this->ProductTable = new ProductTable($this->adapter);
        }
        return $this->ProductTable;
    }
    protected function getCompanyTable()
    {
        if (! $this->CompanyTable)
        {
            $this->CompanyTable = new CompanyTable($this->adapter);
        }
        return $this->CompanyTable;
    }
    protected function getAdsTable()
    {
        if (! $this->AdsTable)
        {
            $this->AdsTable = new AdsTable($this->adapter);
        }
        return $this->AdsTable;
    }
    protected function getTagsTable()
    {
        if (! $this->TagsTable)
        {
            $this->TagsTable = new TagsTable($this->adapter);
        }
        return $this->TagsTable;
    }
    protected function getTagsRelationsTable()
    {
        if (! $this->TagsRelationsTable)
        {
            $this->TagsRelationsTable = new TagsRelationsTable($this->adapter);
        }
        return $this->TagsRelationsTable;
    }
    protected function getActivityTable()
    {
        if (! $this->ActivityTable)
        {
            $this->ActivityTable = new ActivityTable($this->adapter);
        }
        return $this->ActivityTable;
    }
    protected function getViewActivityCompanyTable()
    {
        if (! $this->ViewActivityCompanyTable)
        {
            $this->ViewActivityCompanyTable = new ViewActivityCompanyTable($this->adapter);
        }
        return $this->ViewActivityCompanyTable;
    }
	protected function getViewActivityTable()
    {
        if (! $this->ViewActivityTable)
        {
            $this->ViewActivityTable = new ViewActivityTable($this->adapter);
        }
        return $this->ViewActivityTable;
    }

    protected function getStatisticsDayTable()
    {
        if (! $this->StatisticsDayTable)
        {
            $this->StatisticsDayTable = new StatisticsDayTable($this->adapter);
        }
        return $this->StatisticsDayTable;
    }
}