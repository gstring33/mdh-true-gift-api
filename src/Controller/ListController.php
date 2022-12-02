<?php

namespace App\Controller;

use App\Entity\GiftList;
use App\Model\ListModel;
use App\Services\Decoder;
use Doctrine\Persistence\ManagerRegistry;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/list', name: 'app_list')]
class ListController extends AbstractController
{
    private ValidatorInterface $validator;
    private ManagerRegistry $doctrine;

    public function __construct(
        ValidatorInterface $validator,
        ManagerRegistry $doctrine
    ){
        $this->validator = $validator;
        $this->doctrine = $doctrine;
    }

    #[Route('/{uuid}', name: 'app_list_update', methods: ['PUT'])]
    #[ParamConverter('list', class: 'App\Request\ParamConverter\ListConverter')]
    public function edit(
        Request $request,
        Decoder $decoder,
        SerializerInterface $serializer,
        string $uuid
    ): JsonResponse {
        /** @var GiftList $list */
        $list = $request->attributes->get('list');
        if (!$list) {
            return $this->json(['error'=> 'No List Found'], 404);
        }

        $data = $request->getContent();
        $listModel = $decoder->jsonDecode($data, ListModel::class);
        $list->setIsPublished($listModel->isPublished);

        $error = $this->validateList($list);
        if (!empty($error) || $list->getUuid() !== $uuid) {
            return $this->json(['error' => 'The list could not be updated'], 400);
        }

        $em = $this->doctrine->getManager();
        $em->persist($list);
        $em->flush();

        $json = $serializer->serialize($list, 'json', SerializationContext::create()->setGroups(['list']));
        return new JsonResponse($json, 200, [], true);
    }

    private function validateList (GiftList $list)
    {
        $errors = $this->validator->validate($list);
        if (count($errors) > 0) {
            return ['error' => $errors[0]->getMessage()];
        }
        return [];
    }

}
