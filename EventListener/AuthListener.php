<?php

namespace Crocos\SecurityBundle\EventListener;

use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Crocos\SecurityBundle\Exception\AuthException;
use Crocos\SecurityBundle\Exception\HttpAuthException;
use Crocos\SecurityBundle\Security\AuthChecker;
use Crocos\SecurityBundle\Security\SecurityContext;

/**
 * AuthListener.
 *
 * @author Katsuhiro Ogawa <ogawa@crocos.co.jp>
 */
class AuthListener
{
    /**
     * @var AuthChecker
     */
    protected $checker;

    /**
     * @var SecurityContext
     */
    protected $context;

    /**
     * @var ControllerResolverInterface
     */
    protected $resolver;

    /**
     * Constructor.
     *
     * @param SecurityContext $context
     * @param AuthChecker $checker
     * @param ControllerResolverInterface $resolver
     */
    public function __construct(SecurityContext $context, AuthChecker $checker, ControllerResolverInterface $resolver)
    {
        $this->context = $context;
        $this->checker = $checker;
        $this->resolver = $resolver;
    }

    /**
     * onKernelRequest.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        $controller = $this->resolver->getController($request);

        //無駄になるけどRequestDataCollectorでこけるから...
        //$request->attributes->set('_controller', $controller);

        if (!is_array($controller)) {
            return;
        }

        // If not authenticated, will be thrown AuthException
        $this->checker->authenticate($this->context, $controller[0], $controller[1], $request);
    }

    /**
     * onKernelException.
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();
        $exception = $event->getException();

        if (!$exception instanceof AuthException) {
            return;
        }

        if ($exception instanceof HttpAuthException) {
            if (!$this->context->useHttpAuth()) {
                throw new \InvalidArgumentException(sprintf('Cought a HttpAuthException, but no http auth configuration existing'));
            }

            $response = $this->context->getHttpAuth()->createUnauthorizedResponse($request, $exception);
        } else {
            $forwardingController = $this->context->getForwardingController();
            if (null === $forwardingController) {
                throw new \LogicException('You must configure "forward" attribute in @SecureConfig annotation');
            }

            // Save actual url.
            $this->context->setPreviousUrl($request->getUri());

            $response = $event->getKernel()->forward($forwardingController, $exception->getAttributes());
        }

        $event->setResponse($response);
    }
}
