<?php declare(strict_types=1);

namespace Pehapkari\Controller;

use Nette\Utils\Strings;
use Pehapkari\Blog\PostsProvider;
use Pehapkari\Exception\ShouldNotHappenException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symplify\Statie\Renderable\File\PostFile;

final class BlogController extends AbstractController
{
    /**
     * @var mixed[]
     */
    private $authors = [];

    /**
     * @var PostsProvider
     */
    private $postsProvider;

    /**
     * @param mixed[] $authors
     */
    public function __construct(PostsProvider $postsProvider, array $authors)
    {
        $this->authors = $authors;
        $this->postsProvider = $postsProvider;
    }

    /**
     * @Route(path="/blog/", name="blog")
     */
    public function blog(): Response
    {
        $values = [
            'posts' => $this->postsProvider->provide(),
            'authors' => $this->authors,
        ];

        return $this->render('blog/blog.twig', $values);
    }

    /**
     * @Route(path="/blog/{postSlug}", name="post", requirements={"postSlug"=".+"})
     */
    public function post(string $postSlug): Response
    {
        $matchedPost = $this->matchPostSlug($postSlug);
        if ($matchedPost === null) {
            throw new ShouldNotHappenException();
        }

        return $this->render('blog/post.twig', [
            'post' => $matchedPost,
            'authors' => $this->authors,
        ]);
    }

    private function matchPostSlug(string $postSlug): ?PostFile
    {
        $posts = $this->postsProvider->provide();

        /** @var PostFile $post */
        foreach ($posts as $post) {
            if (Strings::startsWith($post->getRelativeUrl() . '/', 'blog/' . $postSlug)) {
                return $post;
            }
        }

        return null;
    }
}
