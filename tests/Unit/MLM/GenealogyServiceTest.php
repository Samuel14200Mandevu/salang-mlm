<?php

namespace Tests\Unit\MLM;

use App\Models\User;
use App\Models\Genealogy;
use App\Services\MLM\GenealogyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenealogyServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GenealogyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GenealogyService();
    }

    public function test_genealogy_is_created_for_new_user(): void
    {
        $sponsor = User::factory()->create();
        $user = User::factory()->create(['parrain_id' => $sponsor->id]);

        $this->service->createGenealogy($user, $sponsor);

        $genealogy = Genealogy::where('user_id', $user->id)->first();

        $this->assertNotNull($genealogy);
        $this->assertEquals($sponsor->id, $genealogy->sponsor_id);
        $this->assertEquals(1, $genealogy->level);
    }

    public function test_user_can_be_placed_in_genealogy(): void
    {
        $sponsor = User::factory()->create();
        $user = User::factory()->create(['parrain_id' => $sponsor->id]);

        $result = $this->service->placeUser($user, $sponsor);

        $this->assertTrue($result);

        $genealogy = Genealogy::where('user_id', $user->id)->first();
        $this->assertNotNull($genealogy);
        $this->assertEquals($sponsor->id, $genealogy->parent_id);
    }

    public function test_count_descendants(): void
    {
        $root = User::factory()->create();

        // Créer 3 descendants
        for ($i = 0; $i < 3; $i++) {
            $child = User::factory()->create(['parrain_id' => $root->id]);
            $this->service->placeUser($child, $root);
        }

        $count = $this->service->countDescendants($root);

        $this->assertEquals(3, $count);
    }

    public function test_get_tree_structure(): void
    {
        $root = User::factory()->create();

        // Créer des descendants
        $child1 = User::factory()->create(['parrain_id' => $root->id]);
        $this->service->placeUser($child1, $root);

        $child2 = User::factory()->create(['parrain_id' => $root->id]);
        $this->service->placeUser($child2, $root);

        $tree = $this->service->getFullTree($root, 2);

        $this->assertArrayHasKey('user', $tree);
        $this->assertArrayHasKey('children', $tree);
        $this->assertCount(2, $tree['children']);
    }

    public function test_tree_stats(): void
    {
        $root = User::factory()->create();

        for ($i = 0; $i < 3; $i++) {
            $child = User::factory()->create(['parrain_id' => $root->id]);
            $this->service->placeUser($child, $root);
        }

        $stats = $this->service->getTreeStats($root);

        $this->assertArrayHasKey('total_children', $stats);
        $this->assertArrayHasKey('left_count', $stats);
        $this->assertArrayHasKey('right_count', $stats);
        $this->assertArrayHasKey('total_descendants', $stats);

        $this->assertEquals(3, $stats['total_children']);
        $this->assertEquals(3, $stats['total_descendants']);
    }
}