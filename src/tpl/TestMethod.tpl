    /**
     * test: {summary}.
     */
    public function test{name}()
    {
        {declare}
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . {get_token},
        ])->json('{method}', "{url}", {parameters});

        {assert}
    }
