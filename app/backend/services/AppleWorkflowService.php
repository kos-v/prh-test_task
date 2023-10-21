<?php

declare(strict_types=1);

namespace backend\services;

use backend\repositories\AppleRepository;
use common\models\Apple;
use Finite\Event\Callback\CallbackBuilder;
use Finite\Event\FiniteEvents;
use Finite\Event\TransitionEvent;
use Finite\State\State;
use Finite\State\StateInterface;
use Finite\StateMachine\StateMachine;
use Finite\StateMachine\StateMachineInterface;
use Finite\Transition\Transition;
use LogicException;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function time;

class AppleWorkflowService
{
    private const STATE_ON_TREE = 'on_tree';
    private const STATE_ON_GROUND = 'on_ground';
    private const STATE_SPOILED = 'spoiled';
    private const STATE_EATEN = 'eaten';

    private const TR_FALL_TO_GROUND = 'fall_to_ground';
    private const TR_EAT = 'eat';
    private const TR_SPOIL = 'spoil';
    private const TR_DELETE = 'delete';

    public function __construct(private readonly AppleRepository $appleRepository)
    {
    }

    public function canInit(Apple $apple): bool
    {
        return $apple->getIsNewRecord();
    }

    public function init(Apple $apple): Apple
    {
        if (!$this->canInit($apple)) {
            throw new LogicException('The apple cannot be initialized');
        }

        $apple->state = self::STATE_ON_TREE;

        return $apple;
    }

    public function canFallToGround(Apple $apple): bool
    {
        return $this->getStateMachine($apple)->can(self::TR_FALL_TO_GROUND);
    }

    public function fallToGround(Apple $apple): void
    {
        if (!$this->canFallToGround($apple)) {
            throw new LogicException('The apple cannot be falled to the ground');
        }

        $this->applyTransition(self::TR_FALL_TO_GROUND, $apple);
    }

    public function canEat(Apple $apple, int $pieceSize): bool
    {
        return $this->getStateMachine($apple)->can(self::TR_EAT, ['pieceSize' => $pieceSize]);
    }

    public function eat(Apple $apple, int $pieceSize): void
    {
        if (!$this->canEat($apple, $pieceSize)) {
            throw new LogicException('The apple cannot be eat');
        }

        $this->applyTransition(self::TR_EAT, $apple, ['pieceSize' => $pieceSize]);
    }

    public function canSpoil(Apple $apple): bool
    {
        return $this->getStateMachine($apple)->can(self::TR_SPOIL);
    }

    public function spoil(Apple $apple): void
    {
        if (!$this->canSpoil($apple)) {
            throw new LogicException('The apple cannot be spoiled');
        }

        $this->applyTransition(self::TR_SPOIL, $apple);
    }

    public function canDelete(Apple $apple): bool
    {
        return $this->getStateMachine($apple)->can(self::TR_DELETE);
    }

    public function delete(Apple $apple): void
    {
        if (!$this->canDelete($apple)) {
            throw new LogicException('The apple cannot be deleted');
        }

        $this->applyTransition(self::TR_DELETE, $apple);
    }

    private function applyTransition(string $transition, Apple $apple, array $options = []): void
    {
        $this->getStateMachine($apple)->apply($transition, $options);
        $this->appleRepository->save($apple);
    }

    private function getStateMachine(Apple $apple): StateMachineInterface
    {
        $sm = new StateMachine($apple);

        $sm->addState(new State(self::STATE_ON_TREE, StateInterface::TYPE_INITIAL));
        $sm->addState(new State(self::STATE_ON_GROUND, StateInterface::TYPE_NORMAL));
        $sm->addState(new State(self::STATE_SPOILED, StateInterface::TYPE_FINAL));
        $sm->addState(new State(self::STATE_EATEN, StateInterface::TYPE_FINAL));

        $sm->addTransition(new Transition(
            self::TR_FALL_TO_GROUND,
            self::STATE_ON_TREE,
            self::STATE_ON_GROUND
        ));
        $sm->addTransition(new Transition(
            self::TR_EAT,
            self::STATE_ON_GROUND,
            self::STATE_ON_GROUND,
            null,
            (new OptionsResolver())
                ->setRequired('pieceSize')
                ->setAllowedTypes('pieceSize', 'int')
        ));
        $sm->addTransition(new Transition(
            self::TR_SPOIL,
            self::STATE_ON_GROUND,
            self::STATE_SPOILED
        ));
        $sm->addTransition(new Transition(
            self::TR_DELETE,
            self::STATE_ON_GROUND,
            self::STATE_EATEN
        ));

        $sm->getDispatcher()->addListener(
            FiniteEvents::POST_TRANSITION,
            CallbackBuilder::create($sm)
                ->setOn([self::TR_FALL_TO_GROUND])
                ->setCallable([$this, 'onFallToGround'])
                ->getCallback()
        );

        $sm->getDispatcher()->addListener(
            FiniteEvents::TEST_TRANSITION,
            CallbackBuilder::create($sm)
                ->setOn([self::TR_EAT])
                ->setCallable([$this, 'onTestEat'])
                ->getCallback()
        );
        $sm->getDispatcher()->addListener(
            FiniteEvents::POST_TRANSITION,
            CallbackBuilder::create($sm)
                ->setOn([self::TR_EAT])
                ->setCallable([$this, 'onEat'])
                ->getCallback()
        );

        $sm->getDispatcher()->addListener(
            FiniteEvents::TEST_TRANSITION,
            CallbackBuilder::create($sm)
                ->setOn([self::TR_SPOIL])
                ->setCallable([$this, 'onTestSpoil'])
                ->getCallback()
        );

        $sm->getDispatcher()->addListener(
            FiniteEvents::TEST_TRANSITION,
            CallbackBuilder::create($sm)
                ->setOn([self::TR_DELETE])
                ->setCallable([$this, 'onTestDelete'])
                ->getCallback()
        );
        $sm->getDispatcher()->addListener(
            FiniteEvents::POST_TRANSITION,
            CallbackBuilder::create($sm)
                ->setOn([self::TR_DELETE])
                ->setCallable([$this, 'onDelete'])
                ->getCallback()
        );

        $sm->initialize();

        return $sm;
    }

    public function onFallToGround(Apple $apple): void
    {
        $apple->fell_datetime = time();
        $this->appleRepository->save($apple);
    }

    public function onTestEat(Apple $apple, TransitionEvent $event): void
    {
        if ($this->canSpoil($apple) || $apple->integrity - $event->getProperties()['pieceSize'] < 0) {
            $event->reject();
        }
    }

    public function onEat(Apple $apple, TransitionEvent $event): void
    {
        $apple->integrity -= $event->getProperties()['pieceSize'];
        $this->appleRepository->save($apple);
    }

    public function onTestSpoil(Apple $apple, TransitionEvent $event): void
    {
        if (time() < $apple->fell_datetime + 3600 * 10) {
            $event->reject();
        }
    }

    public function onTestDelete(Apple $apple, TransitionEvent $event): void
    {
        if ($apple->integrity !== 0) {
            $event->reject();
        }
    }

    public function onDelete(Apple $apple): void
    {
        $this->appleRepository->remove($apple);
    }
}
