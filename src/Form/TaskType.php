<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Category;
use App\Entity\Project;
use App\Entity\Task;
use App\Enum\TaskPriority;
use App\Enum\TaskStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Название задачи',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Описание',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3],
            ])
            ->add('project', EntityType::class, [
                'label' => 'Проект',
                'class' => Project::class,
                'choice_label' => 'name',
                'placeholder' => 'Выберите проект',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('category', EntityType::class, [
                'label' => 'Категория',
                'class' => Category::class,
                'choice_label' => 'name',
                'placeholder' => 'Без категории',
                'required' => false,
                'attr' => ['class' => 'form-select'],
            ])
            ->add('status', EnumType::class, [
                'label' => 'Статус',
                'class' => TaskStatus::class,
                'choice_label' => static fn (TaskStatus $status): string => $status->value,
                'attr' => ['class' => 'form-select'],
            ])
            ->add('priority', EnumType::class, [
                'label' => 'Приоритет',
                'class' => TaskPriority::class,
                'choice_label' => static fn (TaskPriority $priority): string => $priority->value,
                'attr' => ['class' => 'form-select'],
            ])
            ->add('dueDate', DateType::class, [
                'label' => 'Срок выполнения',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Сохранить',
                'attr' => ['class' => 'btn btn-success mt-3'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Task::class]);
    }
}
