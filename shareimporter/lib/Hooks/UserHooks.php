<?php
namespace OCA\ShareImporter\Hooks;
use OCP\IUserManager;
use OCP\ILogger;
use OCP\Files\External\Service\IUserGlobalStoragesService;
//use OCA\Files_external\Service\UserStoragesService;
//use OCA\Files_External\Service\BackendService;
//use \OCA\Files_External\Service\UserGlobalStoragesService;
use OCP\Files\External\Service\IGlobalStoragesService;
use \OCP\Files\External\IStoragesBackendService;
use OCP\Files\Config\IUserMountCache;
use OC\Files\Mount\MountPoint;
use \OCP\IConfig;
use OCP\Http\Client\IClientService;

class UserHooks {

    private $userManager;
    private $logger;
    private $appName;
    //private $storageService;
    private $backendService;
    private $userGlobalStorageService;
    private $globalStorageService;
    private $userMountCache;
    private $config;
    private $clientService;

    //public function __construct($appName, IUserManager $userManager, ILogger $logger, UserStoragesService $storageService, BackendService $backendService, UserGlobalStoragesService  $globalStorage){
    public function __construct($appName, IUserManager $userManager, ILogger $logger, IUserGlobalStoragesService $userGlobalStorageService, IGlobalStoragesService $globalStorageService, IStoragesBackendService $backendService,IUserMountCache $userMountCache, IConfig $config,IClientService  $clientService){
        $this->appName = $appName;
        $this->userManager = $userManager;
        $this->logger = $logger;
        $this->userGlobalStorageService =  $userGlobalStorageService;
        $this->globalStorageService =  $globalStorageService;

        //$this->storageService = $storageService;
        $this->backendService = $backendService;
        $this->userMountCache = $userMountCache; 
        $this->config = $config;
        $this->clientService = $clientService;
   }

    public function register() {
        $callback = function($user) {
           //$this->logger->info("test test test", array('app' => $this->appName));
           // your code that executes after $user login
           $this->mountShares($user);

        };
        $this->userManager->listen('\OC\User', 'postLogin', $callback);
    }
   



   private function mountShares($user) {
           //$this->logger->info(json_encode($user->getUID()), array('app' => $this->appName));
           $userShares = $this->getUserShares($user);
           $existingUserMounts = $this->getExistingUserMounts($user);
           $existingUserMountsRemain = array();
           foreach ($userShares->shares as $userShare) {
              $foundExistingMount = false;
              foreach($existingUserMounts as $existingUserMount) {
               if($this->isDuplicate($userShare,$existingUserMount)){
                 $existingUserMountsRemain[] = $existingUserMount->getId();
                 $foundExistingMount = true;
                 break;
                }
              }
            if(!$foundExistingMount) {
                $configObj = $this->createMountConfig($user, $userShare->mountpoint, $userShare->host, $userShare->share);
                //$this->logger->info("addStorage" . json_encode($configObj), array('app' => $this->appName));
                $newStorageConfig = $this->globalStorageService->addStorage($configObj);
             
                //$baseId = $newStorageConfig->getId();
                //$newStorage = $this->globalStorageService->getStorage($baseId);
                //$mount = new MountPoint($newStorageConfig, $newStorageConfig->getMountPoint());
		//$this->cache->registerMounts($user, [$mount]);
                //$this->userMountCache->registerMounts($user,array($mount));
                //$this->userMountCache->remoteStorageMounts($newStorageConfig->getId());

            }
           }

                $this->logger->info("remain" . json_encode($existingUserMountsRemain), array('app' => $this->appName));


           foreach($existingUserMounts as $existingUserMount) {
             if( !in_array($existingUserMount->getId(),$existingUserMountsRemain) ) {
                $id = $existingUserMount->getId();
                $this->globalStorageService->removeStorage($id);
                $this->logger->info("removeStorage" . $id . json_encode($existingUserMount), array('app' => $this->appName));

             }
           }
           //$this->logger->info(json_encode($existingUserMounts), array('app' => $this->appName));
           $this->logger->info(json_encode($this->userMountCache->getMountsForUser($user)), array('app' => $this->appName));
           $this->userGlobalStorageService->getAllStorages(); 
  }

  private function getExistingUserMounts($user){
     $existingMounts = $this->userGlobalStorageService->getAllStorages();
     $existingUserMounts = array();
     //$this->logger->info(json_encode($existingMounts), array('app' => $this->appName));

     foreach ($existingMounts as $existingMount) {
           if ( $existingMount->getApplicableUsers() == [$user->getUID()])      {
               $existingUserMounts[] = $existingMount; 
           }
     }
    return $existingUserMounts;

 }

               //$this->globalService->removeStorage($mountId);

   private function getUserSharesJson($user) {
   
       $url = $this->config->getSystemValue("share_importer_webservice_url");
       $api_key = $this->config->getSystemValue("share_importer_webservice_api_key");
       $full_url = $url . "?api_key=" . $api_key . "&user_name=" . $user->getUID();


	try {
			$client = $this->clientService->newClient();
			$raw_response = $client->get(
				$full_url,
				[
					'timeout' => 5,
					'connect_timeout' => 5,
				]
			)->getBody();
                        $this->logger->info($raw_response, array('app' => $this->appName));
                        //return json_decode($raw_response);
                        return $raw_response;

		} catch (\Exception $e) {
                       $this->logger->error($e->getMessage(), array('app' => $this->appName));

			return false;
		}


/*
       $obj = new \stdClass;
       $obj->username = "testuser";
       $obj->shares = array();
       $obj->shares[0] = new \stdClass;
       $obj->shares[0]->mountpoint = "testmount";
       $obj->shares[0]->host = "testhost";
       $obj->shares[0]->share = "testshare";
       $obj->shares[0]->type = "smb";
       $obj->shares[1] = new \stdClass;
       $obj->shares[1]->mountpoint = "testmountXXX" . rand();
       $obj->shares[1]->host = "testhost";
       $obj->shares[1]->share = "testshare2";
       $obj->shares[1]->type = "smb";
       $json = json_encode($obj);
       //$this->logger->info($json, array('app' => $this->appName));
       return $json;
Ã*/
   }


   private function getUserShares($user) {
       $json = $this->getUserSharesJson($user);
       $obj = json_decode($json);
       return $obj;
     
   }    

  private function isDuplicate($userShare,$existingMount) {
      $backend_options = $existingMount->getBackendOptions();
      $this->logger->info(json_encode($backend_options).json_encode($userShare).json_encode($existingMount), array('app' => $this->appName));
      $tmp = "/" . $userShare->mountpoint;
      //$this->logger->info($existingMount->getMountPoint() . "," . $tmp, array('app' => $this->appName));
      //$this->logger->info($userShare->host . "," . $backend_options["host"], array('app' => $this->appName));
      //$this->logger->info($userShare->share . "," . $backend_options["share"], array('app' => $this->appName));

      if($tmp === $existingMount->getMountPoint() &&
       $userShare->host === $backend_options["host"] &&
       $userShare->share === $backend_options["share"]) {
        return true;
     }
     return false;


  }


   //adapted from https://github.com/owncloud/core/blob/master/apps/files_external/lib/Command/Import.php
   private function createMountConfig($user, $mountpoint, $host, $share) {
		$mount = $this->globalStorageService->createConfig();
		//$mount->setId($data['mount_id']);
		$mount->setMountPoint($mountpoint);
		$mount->setBackend($this->getBackendByClass("\OCA\Files_External\Lib\Storage\SMB"));
		$authBackend = $this->backendService->getAuthMechanism("password::sessioncredentials");
		$mount->setAuthMechanism($authBackend);
                $backendOptions = array(); 
                $backendOptions["host"] = $host;
                $backendOptions["share"] = $share;
                $backendOptions["root"] = "";
                $backendOptions["domain"] = "";
		$mount->setBackendOptions($backendOptions);
		//$mount->setMountOptions();
		$mount->setApplicableUsers([$user->getUID()]);
		$mount->setApplicableGroups([]);
		return $mount;
	}

       //adapted from https://github.com/owncloud/core/blob/master/apps/files_external/lib/Command/Import.php
   	private function getBackendByClass($className) {
		$backends = $this->backendService->getBackends();
                //$this->logger->info("backends" . json_encode($backends), array('app' => $this->appName));

		foreach ($backends as $backend) {
                        //$this->logger->info("backend_storage" . $backend->getStorageClass(), array('app' => $this->appName));
			if ($backend->getStorageClass() === $className) {
				return $backend;
			}
		}
	}

}
