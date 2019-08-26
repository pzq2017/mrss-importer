<?php

namespace Tests\Feature;

use App\Http\Resources\Mrss as MrssResource;
use App\Http\Resources\Entry as EntryResource;
use App\Models\Entry;
use App\Models\Mrss;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Lumen\Testing\DatabaseMigrations;
use TestCase;

class MrssTest extends TestCase
{
    use DatabaseMigrations;

    private $user;
    private $mrss;
    private $entry;

    public function setUp()
    {
        parent::setUp();
        $this->user = factory(User::class)->create([
            'password' => Hash::make('ABCabc01'),
        ]);
        $this->mrss = factory(Mrss::class)->create([
            'user_id' => $this->user->id,
        ]);
        $this->entry = factory(Entry::class)->create([
            'mrss_id' => $this->mrss->id,
        ]);
    }

    private function assertUnauthorizedAccess($method, $url)
    {
        $this->json($method, $url, [])->seeJson([
            'status' => 'error',
            'message'=> 'Token not provided',
        ])->assertResponseStatus(200);
    }

    private function actingAsUser()
    {
        $response = $this->json('POST', '/api/login', ['email' => $this->user->email, 'password' => 'ABCabc01']);
        $message = json_decode($response->response->getContent(), true);
        return $message['data']['token'];
    }

    private function assertActingAsUserAccess($method, $url, $data=[], $responseData=[], $showPagination=false)
    {
        $response = $this->json($method, $url, $data, ['Authorization' => 'Bearer '.$this->actingAsUser()]);
        $response->seeJson([
            'status' => 'success',
            'data' => $responseData
        ]);
        if ($showPagination) {
            $response->seeJsonStructure([
                'links' => [
                    'first', 'last', 'prev', 'next'
                ],
                'meta' => [
                    'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total'
                ]
            ]);
        }
        return $response->assertResponseStatus(200);
    }

    private function assertActingAsUserStoreMrssDatas($method, $url, $data=[], $expectReponseStatus, $expectResponseMessage=[])
    {
        $response = $this->json($method, $url, $data, ['Authorization' => 'Bearer '.$this->actingAsUser()]);
        if ($expectReponseStatus == 'error') {
            $response->seeJson([
                'status' => $expectReponseStatus,
            ])->seeJsonStructure([
                'message'=> $expectResponseMessage
            ]);
        } else {
            $response->seeJson([
                'status' => $expectReponseStatus,
            ]);
        }
        return $response->assertResponseStatus(200);
    }

    /**
     * @test
     */
    public function not_authenticated_user_cannot_access()
    {
        $this->assertUnauthorizedAccess('GET', '/api/mrss');
        $this->assertUnauthorizedAccess('POST', '/api/mrss');
        $this->assertUnauthorizedAccess('GET', '/api/mrss/'.$this->mrss->id);
        $this->assertUnauthorizedAccess('PUT', '/api/mrss/'.$this->mrss->id);
        $this->assertUnauthorizedAccess('DELETE', '/api/mrss/'.$this->mrss->id);
        $this->assertUnauthorizedAccess('PUT', '/api/mrss/'.$this->mrss->id.'/action');
        $this->assertUnauthorizedAccess('GET', '/api/mrss/'.$this->mrss->id.'/entries');
        $this->assertUnauthorizedAccess('GET', '/api/mrss/'.$this->mrss->id.'/entry/'.$this->entry->id);
    }

    /**
     * @test
     */
    public function get_mrss_success()
    {
        $mrss1 = factory(Mrss::class)->create([
            'title' => 'mrss1 title',
            'user_id' => $this->user->id,
            'status' => Mrss::STATUS_STOPED
        ]);

        $mrss1->entries_count = 0;
        $this->mrss->entries_count = 1;
        $data = json_decode((new Response(new MrssResource($this->mrss)))->getContent(), true);
        $data1 = json_decode((new Response(new MrssResource($mrss1)))->getContent(), true);

        $this->assertActingAsUserAccess('GET', '/api/mrss', ['status' => '', 'keyword' => ''], [$data, $data1], true);
        $this->assertActingAsUserAccess('GET', '/api/mrss', ['status' => Mrss::STATUS_STOPED, 'keyword' => ''], [$data1], true);
        $this->assertActingAsUserAccess('GET', '/api/mrss', ['status' => '', 'keyword' => $mrss1->title], [$data1], true);
    }

    /**
     * @test
     */
    public function get_mrss_info_success()
    {
        $data = json_decode((new Response(new MrssResource($this->mrss)))->getContent(), true);
        $this->assertActingAsUserAccess('GET', '/api/mrss/'.$this->mrss->id, [], $data);
    }

    /**
     * @test
     */
    public function get_mrss_entries_success()
    {
        $entry1 = factory(Entry::class)->create([
            'mrss_id' => $this->mrss->id,
            'title' => 'entry title',
            'status' => 'failed'
        ]);

        $data1 = json_decode((new Response(new EntryResource($entry1)))->getContent(), true);
        $data = json_decode((new Response(new EntryResource($this->entry)))->getContent(), true);

        $this->assertActingAsUserAccess('GET', '/api/mrss/'.$this->mrss->id.'/entries', ['status' => '', 'keyword' => ''], [$data, $data1], true);
        $this->assertActingAsUserAccess('GET', '/api/mrss/'.$this->mrss->id.'/entries', ['status' => 'failed', 'keyword' => ''], [$data1], true);
        $this->assertActingAsUserAccess('GET', '/api/mrss/'.$this->mrss->id.'/entries', ['status' => '', 'keyword' => $this->entry->title], [$data], true);
    }

    /**
     * @test
     */
    public function get_mrss_entry_info_success()
    {
        $data = json_decode((new Response(new EntryResource($this->entry)))->getContent(), true);
        $this->assertActingAsUserAccess('GET', '/api/mrss/'.$this->mrss->id.'/entry/'.$this->entry->id, [], $data);
    }

    /**
     * @test
     */
    public function create_new_mrss_missing_required_values_failed()
    {
        $this->assertActingAsUserStoreMrssDatas('POST', '/api/mrss', [], 'error', ['title', 'url']);
    }

    /**
     * @test
     */
    public function create_new_mrss_invalid_url_failed()
    {
        $this->assertActingAsUserStoreMrssDatas('POST', '/api/mrss', [
            'title' => 'title',
            'url' => 'url'
        ], 'error', ['url']);
    }

    /**
     * @test
     */
    public function create_new_mrss_exists_url_failed()
    {
        $this->assertActingAsUserStoreMrssDatas('POST', '/api/mrss', [
            'title' => 'title',
            'url' => $this->mrss->url
        ], 'error', ['url']);
    }

    /**
     * @test
     */
    public function create_new_mrss_success()
    {
        $title = 'mrss title';
        $url = 'https://test.com/mrss.xml';

        $this->assertActingAsUserStoreMrssDatas('POST', '/api/mrss', [
            'title' => $title,
            'url' => $url,
            'initial_import_number' => 1,
            'auto_import_new' => 1,
        ], 'success');

        $this->assertNotNull(Mrss::where('title', $title)->where('url', $url)->first());
    }

    /**
     * @test
     */
    public function update_mrss_missing_required_values_failed()
    {
        $this->assertActingAsUserStoreMrssDatas('PUT', '/api/mrss/'.$this->mrss->id, [
            'user_id' => $this->user->id,
        ], 'error', ['title', 'url']);
    }

    /**
     * @test
     */
    public function update_mrss_with_existed_url_failed()
    {
        $mrss1 = factory(Mrss::class)->create();

        $title = 'mrss title';
        $this->assertActingAsUserStoreMrssDatas('PUT', '/api/mrss/'.$this->mrss->id, [
            'title' => $title,
            'url' => $mrss1->url,
        ], 'error', ['url']);

        $this->assertNotEquals($this->mrss->fresh()->title, $title);
    }

    /**
     * @test
     */
    public function update_mrss_success()
    {
        $title = 'mrss title';
        $url = 'https://test.com/mrss.xml';

        $this->assertActingAsUserStoreMrssDatas('PUT', '/api/mrss/'.$this->mrss->id, [
            'title' => $title,
            'url' => $url,
            'auto_import_new' => 1
        ], 'success');

        $this->assertEquals($this->mrss->fresh()->title, $title);
        $this->assertEquals($this->mrss->fresh()->url, $url);
        $this->assertEquals($this->mrss->fresh()->auto_import_new, 1);
    }

    /**
     * @test
     */
    public function delete_mrss_success()
    {
        $this->json('DELETE', '/api/mrss/'.$this->mrss->id, [], ['Authorization' => 'Bearer '.$this->actingAsUser()])
            ->seeJson([
                'status' => 'success',
            ])->assertResponseStatus(200);
    }

    /**
     * @test
     */
    public function set_mrss_status_with_invalid_params_failed()
    {
        $this->json('PUT', '/api/mrss/'.$this->mrss->id.'/action', ['action' => 'status'], ['Authorization' => 'Bearer '.$this->actingAsUser()])
            ->seeJson([
                'status' => 'error',
            ])
            ->seeJsonStructure([
                'message' => ['action']
            ])
            ->assertResponseStatus(200);
    }

    /**
     * @test
     */
    public function  set_mrss_status_success()
    {
        $this->json('PUT', '/api/mrss/'.$this->mrss->id.'/action', ['action' => Mrss::STATUS_STOPED], ['Authorization' => 'Bearer '.$this->actingAsUser()])
            ->seeJson([
                'status' => 'success',
            ])
            ->assertResponseStatus(200);

        $this->assertEquals($this->mrss->fresh()->status, Mrss::STATUS_STOPED);
    }
}
