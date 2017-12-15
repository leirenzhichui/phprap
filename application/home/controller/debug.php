<?php

namespace app\home\controller;

use app\api;
use gophp\controller;
use gophp\curl;
use gophp\request;
use gophp\response;

class debug extends controller {

    // 获取接口id
    public function index()
    {
        $api     = request::post('api', []);
        $request = request::post('request', []);
        $header = request::post('header', []);

        if(!$url = $api['url']){

            response::ajax(['code'=> 300, 'msg' => '请求地址不存在']);

        }

        if(!$method = $api['method']){

            response::ajax(['code'=> 300, 'msg' => '请求方式不存在']);

        }

        $data = [];
        $header_data = [];

        foreach ($request as $k=>$v){
            foreach ($v as $k1=>$v1){
                $data[$request['key'][$k1]] = $v1;
            }
        }

        foreach ($header['value'] as $k1=>$v1){
            $header_data[] = $header['key'][$k1].':'.$v1;
        }

        $header_data = ['Authorization:Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImVmYjJhYzk5ZWRiZjI1M2ViNTQ1YzQ4M2QwMDkxYjdkY2UyMDMyNDYyOTUwMTJmYzkzOTQxZTA2ZTU3MTBmMGE4MzZmM2IyYjMxMGQ3OGMwIn0.eyJhdWQiOiIyIiwianRpIjoiZWZiMmFjOTllZGJmMjUzZWI1NDVjNDgzZDAwOTFiN2RjZTIwMzI0NjI5NTAxMmZjOTM5NDFlMDZlNTcxMGYwYTgzNmYzYjJiMzEwZDc4YzAiLCJpYXQiOjE1MTMzMjM3OTksIm5iZiI6MTUxMzMyMzc5OSwiZXhwIjoxNTE1OTE1Nzk5LCJzdWIiOiIyMjAiLCJzY29wZXMiOltdfQ.AoGgErup2BKemNi6j_FjuGvksXrpwgUKBEWFstkLYmusOQX0fAMHAZMbPIxGIgTM4z4977d5X_dYvxMBba6rwUvC_scdPksB2CQQG7L0h9UQ1l3U8WVuOVCW0b-EpeCeyBAP6qRrx55_ITcFu0P9LtoBCKYc3n1lKfU_Ft-7rTAcspuU_dcPxdRsmF3xgn1YCYzPbsEBBTZ5rtwDEHqhE8ijjwoaKPMST9x9VKmflzG-fDh1H5t6QzX67Cuh5BezgN1tZR3xCjKE_UeQ6-7yvhs3P9W_jpyzCaFw-a4pUKXR8pFJxyvSUv9nyJpMZIfLXmnjhdXOklORhXrJJAStsjNE-iIP75AYaI4x6FcruwuyNB-AUVl_f5FYy765li3fedyPtGtHuopEQ8yi2lPvh9zkSuo-tmAlBTpZq3WeT5W_wU6H5_Y0E-j-EbWWA86PnScEB2B8XJ5DOcIktI_H0pvSDUGDrEtTE3O6NabQMTqxk53qs6f6UmbLufY2hs0EE4Gj4uiZNomjQDwJbIyrwGtwGaU0Zzd-St9LorsYCZK-ucaOlkVTb7pl5vLo1JR--194eeQy6eqp-3OxjAMzSrjKi7pNoiWGlOUfBjhlpuCfzLHSF-mNYUYOkQnOW8C7d4_Mz97eezlNtuo2ybbFjUGKAd_BbFoDdjodttxE0vs'];
        dump($header_data);

        $curl = new curl($url, $method, $data, $header_data);

        if($info = $curl->getInfo()){

            $info = serialize($info);

        }

        if($body = $curl->getBody()){
            $body = serialize($body);
        }

        if($header = $curl->getHeader()){
            $header = serialize($header);
        }

        response::ajax(['info' => $info,'header' => $header,'body' => $body]);

    }

    public function load()
    {

        $info = request::post('info', '');
        $header = request::post('header', '');
        $body = request::post('body', '');

        if($info){

            $this->assign('info', unserialize($info));

        }

        if($header){

            $this->assign('headers', unserialize($header));

        }

        if($body){

            $this->assign('body', unserialize($body));

        }

        $this->display('debug/load');

    }

    // 获取接口详情
    public function __call($name, $arguments)
    {

        $id  = id_decode($this->action);

        $api = api::get_api_info($id);

        if(!$api){

            $this->error('该接口不存在');

        }

        $project = api::get_project_info($id);

        // 获取项目环境域名
        $envs    = json_decode($project['envs'], true);

        foreach ($envs as $k => $env) {
            $envs[$k]['name'] = $env['name'];
            $envs[$k]['title'] = $env['title'];
            $envs[$k]['url'] = $env['domain'] . '/' . $api['uri'];
        }

        $encode_id = id_encode($api['id']);

        $mock = [
            'name' => 'mock',
            'title' => '虚拟地址',
            'url' => url("mock/$encode_id", '', true),
        ];

        array_unshift($envs, $mock);

        // 获取请求参数列表
        $request_fields = \app\field::get_field_list($id, 1);

        // 获取header参数列表
        $header_fields = \app\field::get_field_list($id, 3);

        $methods = \app\api::get_method_list();

        $this->assign('api', $api);
        $this->assign('envs', $envs);
        $this->assign('methods', $methods);

        $this->assign('request_fields', $request_fields);
        $this->assign('header_fields', $header_fields);

        $this->display('debug/detail');

    }

}