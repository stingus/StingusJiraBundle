<?php

namespace Stingus\JiraBundle\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Stingus\JiraBundle\Model\OauthTokenInterface;

/**
 * Class OauthTokenManager
 *
 * @package Stingus\JiraBundle\Doctrine
 */
class OauthTokenManager
{
    const SERVICE_ID = 'stingus_jira.oauth_token_manager';

    /** @var ObjectManager */
    private $objectManager;

    /** @var ObjectRepository */
    private $repository;

    /**
     * OauthTokenManager constructor.
     *
     * @param ObjectManager $objectManager
     * @param string        $oauthTokenClassName
     */
    public function __construct(ObjectManager $objectManager, string $oauthTokenClassName)
    {
        $this->objectManager = $objectManager;
        $this->repository = $objectManager->getRepository($oauthTokenClassName);
    }

    /**
     * @return ObjectRepository
     */
    public function getRepository(): ObjectRepository
    {
        return $this->repository;
    }

    /**
     * @param OauthTokenInterface $oauthToken
     */
    public function save(OauthTokenInterface $oauthToken)
    {
        $this->objectManager->persist($oauthToken);
        $this->objectManager->flush();
    }
}
