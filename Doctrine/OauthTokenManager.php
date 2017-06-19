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
     * @param string $consumerKey
     *
     * @return null|OauthTokenInterface
     */
    public function findByConsumerKey(string $consumerKey)
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */

        return $this->repository->findOneBy(['consumerKey' => $consumerKey]);
    }

    /**
     * @param OauthTokenInterface $oauthToken
     */
    public function save(OauthTokenInterface $oauthToken)
    {
        /** @var OauthTokenInterface $existingToken */
        $existingToken = $this->repository->findOneBy(['consumerKey' => $oauthToken->getConsumerKey()]);

        if (null !== $existingToken) {
            $oauthToken->setId($existingToken->getId());
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $oauthToken = $this->objectManager->merge($oauthToken);
        }

        if (null === $existingToken) {
            $this->objectManager->persist($oauthToken);
        }

        $this->objectManager->flush();
    }
}
