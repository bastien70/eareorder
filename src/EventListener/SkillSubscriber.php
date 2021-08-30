<?php

namespace App\EventListener;

use App\Entity\Skill;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SkillSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SkillRepository $skillRepository
    )
    {}

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => 'beforePersistedEvent'
        ];
    }

    public function beforePersistedEvent(BeforeEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();

        if(!($entity instanceof Skill))
        {
            return;
        }

        $entity->setPosition(count($this->skillRepository->findAll()));
    }
}