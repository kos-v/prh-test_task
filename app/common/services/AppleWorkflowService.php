<?php

declare(strict_types=1);

namespace common\services;

use common\models\Apple;
use common\repositories\AppleRepository;
use common\valueObjects\Percent;
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

use function func_get_args;
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

    public function __construct(
        private readonly AppleRepository $appleRepository,
        private readonly int $appleFreshnessTime,
    ) {
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

    public function canEat(Apple $apple, Percent $pieceSize): bool
    {
        return $this->getStateMachine($apple)->can(self::TR_EAT, [
            'pieceSize' => $pieceSize->toBankingFormat()
        ]);
    }

    public function eat(Apple $apple, Percent $pieceSize): void
    {
        if (!$this->canEat($apple, $pieceSize)) {
            throw new LogicException('The apple cannot be eat');
        }

        $this->applyTransition(self::TR_EAT, $apple, [
            'pieceSize' => $pieceSize->toBankingFormat()
        ]);
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

    private function defineStateMachineHandlers(StateMachineInterface $sm): StateMachineInterface
    {
        $sm->getDispatcher()->addListener(
            FiniteEvents::POST_TRANSITION,
            CallbackBuilder::create($sm)
                ->setOn([self::TR_FALL_TO_GROUND])
                ->setCallable(fn () => $this->onFallToGround(...func_get_args()))
                ->getCallback()
        );

        $sm->getDispatcher()->addListener(
            FiniteEvents::TEST_TRANSITION,
            CallbackBuilder::create($sm)
                ->setOn([self::TR_EAT])
                ->setCallable(fn () => $this->onTestEat(...func_get_args()))
                ->getCallback()
        );
        $sm->getDispatcher()->addListener(
            FiniteEvents::POST_TRANSITION,
            CallbackBuilder::create($sm)
                ->setOn([self::TR_EAT])
                ->setCallable(fn () => $this->onEat(...func_get_args()))
                ->getCallback()
        );

        $sm->getDispatcher()->addListener(
            FiniteEvents::TEST_TRANSITION,
            CallbackBuilder::create($sm)
                ->setOn([self::TR_SPOIL])
                ->setCallable(fn () => $this->onTestSpoil(...func_get_args()))
                ->getCallback()
        );

        $sm->getDispatcher()->addListener(
            FiniteEvents::TEST_TRANSITION,
            CallbackBuilder::create($sm)
                ->setOn([self::TR_DELETE])
                ->setCallable(fn () => $this->onTestDelete(...func_get_args()))
                ->getCallback()
        );
        $sm->getDispatcher()->addListener(
            FiniteEvents::POST_TRANSITION,
            CallbackBuilder::create($sm)
                ->setOn([self::TR_DELETE])
                ->setCallable(fn () => $this->onDelete(...func_get_args()))
                ->getCallback()
        );

        return $sm;
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

        $this->defineStateMachineHandlers($sm)->initialize();

        return $sm;
    }

    private function onFallToGround(Apple $apple): void
    {
        $apple->fell_datetime = time();
        $this->appleRepository->save($apple);
    }

    private function onTestEat(Apple $apple, TransitionEvent $event): void
    {
        if ($this->canSpoil($apple) || $apple->integrity - $event->getProperties()['pieceSize'] < 0) {
            $event->reject();
        }
    }

    private function onEat(Apple $apple, TransitionEvent $event): void
    {
        $apple->integrity -= $event->getProperties()['pieceSize'];
        $this->appleRepository->save($apple);
    }

    private function onTestSpoil(Apple $apple, TransitionEvent $event): void
    {
        if (time() < $apple->fell_datetime + $this->appleFreshnessTime) {
            $event->reject();
        }
    }

    private function onTestDelete(Apple $apple, TransitionEvent $event): void
    {
        if ($apple->integrity !== 0) {
            $event->reject();
        }
    }

    private function onDelete(Apple $apple): void
    {
        $this->appleRepository->remove($apple);
    }
}
