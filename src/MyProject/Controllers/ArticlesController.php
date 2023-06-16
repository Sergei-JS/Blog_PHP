<?php

namespace MyProject\Controllers;


use MyProject\Controllers\AbstractController;
use MyProject\Exceptions\ForbiddenException;
use MyProject\Exceptions\InvalidArgumentException;
use MyProject\Exceptions\NotFoundException;
use MyProject\Exceptions\UnauthorizedException;
use MyProject\Models\Articles\Article;

class ArticlesController extends AbstractController
{

    public function view(int $articleId): void
    {
        $article = Article::getById($articleId);

        if ($article === null) {
            throw new NotFoundException();
        }

        $this->view->renderHtml('articles/view.php', [
            'article' => $article
        ]);
    }

//    public function edit (int $articleId) : void{
//        /** @var Article $article */
//        $article = Article::getById($articleId);
//
//        if ($article === null) {
//            $this->view->renderHtml('errors/404.php', [], 404);
//            return;
//        }
//        $article->setName('Новое название статьи');
//        $article->setText('Новый текст статьи');
//        $article->save();
//
//      $this->view->renderHtml('articles/view.php', [
//            'article' => $article
//        ]);
//    }

//изменние статьи через форму
    public function edit (int $articleId) : void{
        /** @var Article $article */
        $article = Article::getById($articleId);

        if ($article === null) {
        throw new NotFoundException();
            }
        if ($this->user===null){
        throw new UnauthorizedException();
        }
        if (!$this->user->isAdmin()){
            throw new ForbiddenException('Для изменения статьи нужно обладать правами администратора');
        }
            if (!empty($_POST)){
                try {
                    $article->updateFromArray($_POST);
                }catch (InvalidArgumentException $e){
                    $this->view->renderHtml('articles/edit.php', ['error' => $e->getMessage(), 'article' => $article]);
                    return;
                }
                header('Location: /articles/' . $article->getId(), true, 302);
                exit();
            }
        $this->view->renderHtml('articles/edit.php', ['article'=>$article]);
    }


//    public function add(): void
//    {
//        $author = User::getById(1);
//
//        $article = new Article();
//        $article->setAuthor($author);
//        $article->setName('Новое название статьи');
//        $article->setText('Новый текст статьи');
//
//        $article->save();
//
//        var_dump($article);
//    }



    public function add(): void{
        if ($this->user===null){
         throw new UnauthorizedException();
        }
        if (!$this->user->isAdmin()){
           throw new ForbiddenException('Для добавления статьи нужно обладать правами администратора');
        }

        if (!empty($_POST)) {
            try {
                $article = Article::createFrom($_POST, $this->user);
            } catch (InvalidArgumentException $e) {
                $this->view->renderHtml('articles/add.php', ['error' => $e->getMessage()]);
                return;
            }
            header('Location:/articles/'.$article->getId(),true,302);
            exit();

        }
        $this->view->renderHtml('articles/add.php');

    }

    public function del($articleId): void
    {

        $article = Article::getById($articleId);
        if ($article) {
            $article->delete();
            echo ' Статья удалена';
        } else {
            echo ' Статьи с таким id не существует';

        }
    }}
