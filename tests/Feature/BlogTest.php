<?php

namespace Tests\Feature;

use App\Models\Blogpost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use function substr_count;

/**
 * All of these tests only work with the given blog post entries that the
 * migrations have put into the DB.
 *
 * GIVEN: The existing posts in the database.
 */
class BlogTest extends TestCase
{
    use RefreshDatabase;

    /** Get the number of posts from the response. */
    private function getBlogpostsCount($response) {
        return $response->baseResponse->original->getData()['blogposts']->count();
    }

    /**
     * Get the landing page
     */
    public function testBlogLandingPage() {
        $response = $this->get(route('blog.all'));
        $response->assertSuccessful();

        // Paginating at 5, this should not exceed the 5
        $this->assertEquals($this->getBlogpostsCount($response), 5);
    }

    /**
     * Test a Category page.
     */
    public function testCategoryPage() {
        $response = $this->get(route('blog.category', ['category' => 'Fehlerbehebung']));
        $response->assertSuccessful(); // There is at least one post with category `Fehlerbehebung`.
        $this->assertNotEquals($this->getBlogpostsCount($response), 0);

        // We expect to see a page title with the expected category name
        $response->assertSee('<h1><i class="fa fa-tag pr-2"></i>Fehlerbehebung</h1>', false);

        // This is likely to fail when we start to use the TAG icon with Category names in our blog posts.
        // In this case, please get add a space or a zero-width space (&#x200b;) between those words.
        // We assert that there will be a case of a TAG icon with the category name
        $this->assertNotEquals(substr_count($this->trimHtml($response),
                        "<i class=\"fa fa-tag\"></i>Fehlerbehebung</a></li>"), 0);
        // We assert that there won't be a TAG icon next to another category name.
        $this->assertEquals(substr_count($this->trimHtml($response),
                     "<i class=\"fa fa-tag\"></i>Bekanntmachungen</a></li>"), 0);
    }

    function trimHtml($response) {
        $t = implode("\n", array_map('trim', explode("\n", $response->content())));
        return str_replace(["\r", "\n"], "", $t);
    }


    /**
     * If the requested category does not have any posts, we expect a 404 Not Found error.
     */
    public function testCategoryEmpty() {
        $response = $this->get(route('blog.category', ['category' => 'non-existing-category']));
        $response->assertStatus(404);
        $response->assertSee('Not Found');
    }

    /**
     * Test the rendering of a single blog post. The user should be able to see
     * every post, so why not test every post?
     */
    function testSingleView() {
        BlogPost::all()->map(function($blogpost) {
            $response = $this->get(route('blog.show', ['slug' => $blogpost->slug]));
            $response->assertOk();
            $response->assertSee("<h3 class=\"mb-0\">" . e($blogpost->title) . "</h3>", false);
        });
    }

    /**
     * If the requested post does not exist, we expect a 404 error.
     */
    function testSingleEmpty() {
        $response = $this->get(route('blog.show', ['slug' => 'non-existing-post']));
        $response->assertStatus(404);
        $response->assertSee('Not Found');
    }
}
