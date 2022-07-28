<?php

namespace Tests\Feature;

use Tests\TestCase;

class TestTest extends TestCase
{

    /**
     * test: 删除专辑.
     */
    public function testDeleteAlbum()
    {
		$uuid = \App\Models\Album::query()->value("uuid");
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . app(Helper::class)->getAccessTokenForEmailToOrg(),
        ])->json('delete', "/album/{$uuid}", []);

        $response->assertStatus(204);
    }

    /**
     * test: 专辑详情.
     */
    public function testGetAlbum()
    {
		$uuid = \App\Models\Album::query()->value("uuid");
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . app(Helper::class)->getAccessTokenForEmailToOrg(),
        ])->json('get', "/album/{$uuid}", []);

        $response->assertStatus(200)
			->assertJsonStructure([
				'status', 'code', 'data' => [
					'id', 'uuid', 'name', 
					'version', 'type', 'label', 'artists', 
					'language', 'genre', 'second_genre', 'area', 
					'description', 'cover', 'full_cover', 
				], 
			]);
    }

    /**
     * test: 编辑专辑.
     */
    public function testPutAlbum()
    {
		$uuid = \App\Models\Album::query()->value("uuid");
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . app(Helper::class)->getAccessTokenForEmailToOrg(),
        ])->json('put', "/album/{$uuid}", [
			'name' => '屠建',
			'version' => '刘子安',
			'type' => 1,
			'label' => 89,
			'artist_ids' => [
				4, 59, 
			],
			'language_id' => 30,
			'area' => 53,
			'genre_id' => 9,
			'second_genre_id' => 75,
			'description' => '宗刚',
			'cover' => '蔺楠',
			'method_type' => 1,
		]);

        $response->assertStatus(200);
    }

    /**
     * test: 添加专辑.
     */
    public function testPostAlbum()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . app(Helper::class)->getAccessTokenForEmailToOrg(),
        ])->json('post', "/album", [
			'name' => '宗洁',
		]);

        $response->assertStatus(201);
    }

    /**
     * test: 专辑列表.
     */
    public function testGetAlbums()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . app(Helper::class)->getAccessTokenForEmailToOrg(),
        ])->json('get', "/albums", []);

        $response->assertStatus(200)
			->assertJsonStructure([
				'status', 'code', 'data' => [
					'items' => [
						'*' => [
							'id', 'uuid', 'name', 
							'type', 'status', 'full_cover', 'label', 
							'version', 'creator', 'platform', 'song_count', 
							'upc', 'issue_area', 'issue_time', 'created_at', 
						], 
					], 
				], 
			]);
    }

}