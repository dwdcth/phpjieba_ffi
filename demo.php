<?php

const DICT_PATH = "./dict/jieba.dict.utf8";
const HMM_PATH = "./dict/hmm_model.utf8";
const USER_DICT = "./dict/user.dict.utf8";
const IDF_PATH = "./dict/idf.utf8";
const STOP_WORDS_PATH = "./dict/stop_words.utf8";

$loadffi = microtime(true);
$jieba = FFI::load("jieba.h");
$endloadffi = microtime(true);
printf("load: " . ($endloadffi - $loadffi) . "\n");

function CutDemo($jieba)
{

    printf("CutDemo:\n");
    // init will take a few seconds to load dicts.
    $handle = $jieba->NewJieba(DICT_PATH, HMM_PATH, USER_DICT, IDF_PATH, STOP_WORDS_PATH);

    $s = "南京市长江大桥";
    $len = strlen($s);

    $words = $jieba->Cut($handle, $s, $len);

    for ($x = $words; !FFI::isNull($x) && !FFI::isNull($x->word); $x++) {
        printf(substr($x->word, 0, $x->len) . "\n");
    }
    $jieba->FreeWords($words);
    $jieba->FreeJieba($handle);
}


function CutWithoutTagNameDemo($jieba)
{
    printf("CutWithoutTagNameDemo:\n");
    // init will take a few seconds to load dicts.
    $handle = $jieba->NewJieba(DICT_PATH, HMM_PATH, USER_DICT, IDF_PATH, STOP_WORDS_PATH);

    $s = "我是拖拉机学院手扶拖拉机专业的。不用多久，我就会升职加薪，当上CEO，走上人生巅峰。";
    $len = strlen($s);
    $words = $jieba->CutWithoutTagName($handle, $s, $len, "x");
    for ($x = $words; !FFI::isNull($x->word); $x++) {
        printf(substr($x->word, 0, $x->len) . "\n");
    }
    $jieba->FreeWords($words);
    $jieba->FreeJieba($handle);
}

function ExtractDemo($jieba)
{
    printf("ExtractDemo:\n");

    // init will take a few seconds to load dicts.
    $handle = $jieba->NewExtractor(DICT_PATH,
        HMM_PATH,
        IDF_PATH,
        STOP_WORDS_PATH,
        USER_DICT);

    $s = "我是拖拉机学院手扶拖拉机专业的。不用多久，我就会升职加薪，当上CEO，走上人生巅峰。";
    $top_n = 5;
    $words = $jieba->Extract($handle, $s, strlen($s), $top_n);
    for ($x = $words; !FFI::isNull($x) && !FFI::isNull($x->word); $x++) {
        printf(substr($x->word, 0, $x->len) . "\n");
    }
    $jieba->FreeWords($words);
    $jieba->FreeExtractor($handle);
}

function UserWordDemo($jieba)
{
    printf("UserWordDemo:\n");
    $handle = $jieba->NewJieba(DICT_PATH, HMM_PATH, USER_DICT, IDF_PATH, STOP_WORDS_PATH);

    $s = "人艰不拆";
    $len = strlen($s);

    $words = $jieba->Cut($handle, $s, $len);

    for ($x = $words; !FFI::isNull($x) && !FFI::isNull($x->word); $x++) {
        printf(substr($x->word, 0, $x->len) . "\n");
    }
    $jieba->FreeWords($words);

    $jieba->JiebaInsertUserWord($handle, "人艰不拆");
    $words = $jieba->Cut($handle, $s, $len);
    for ($x = $words; !FFI::isNull($x->word); $x++) {
        printf(substr($x->word, 0, $x->len) . "\n");
    }
    $jieba->FreeWords($words);
    $jieba->FreeJieba($handle);
}

for ($i = 0; $i < 20; $i++) {
    CutDemo($jieba);
    CutWithoutTagNameDemo($jieba);
    ExtractDemo($jieba);
    UserWordDemo($jieba);
}