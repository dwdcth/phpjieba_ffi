# phpjieba_ffi
使用PHP 7.4的 FFI 测试直接调用cjieba分词的动态库 


选用CJieba的原因是FFI使用的是C的调用约定，如果用Cpp，还得自己包装一下，然后extern C,让编译器生成标准C的动态库。

# 碰到的问题
## 段错误
- C变量没有初始化
- 直接调用了C的函数，没有通过FFI 初始化后的的C对象调用
- 非空判断 需要使用 FFI::isNull($x)
- 指针形式的数组 不能用foreach

## 指针形式数组的循环
查看C代码发现Cut部分如下：
```C
CJiebaWord* Cut(Jieba handle, const char* sentence, size_t len) {
  cppjieba::Jieba* x = (cppjieba::Jieba*)handle;
  vector<string> words;
  string s(sentence, len);
  x->Cut(s, words);
  
  CJiebaWord* res = (CJiebaWord*)malloc(sizeof(CJiebaWord) * (words.size() + 1));
  size_t offset = 0;
  for (size_t i = 0; i < words.size(); i++) {
    res[i].word = sentence + offset;
    res[i].len = words[i].size();
    offset += res[i].len;
  }
  if (offset != len) {
    free(res);
    return NULL;
  }
  res[words.size()].word = NULL;
  res[words.size()].len = 0;
  return res;
}
```
返回的是一个结构体指针，在C语言里，数组名实际是数组第一个变量的指针地址，所以可以通过指针地址++的操作来遍历，在FFI里面呢？

对于这个数组，我一开始用foreach 循环，直接报段错误了,后来和C一样，直接用指针++，发现是可行的，这里给FFI点赞，居然也可以直接操作C指针。

## 分词结果获取
如上面的代码，对于单个分词CJiebaWord，也不是保存的分词，而是`sentence + offset`，就是说第一个分词结果肯定是原始字符串。
 
在C的demo里是printf格式化(*.* 表示字段宽度和对齐)，但是PHP里没有类似的方法，需要截取字符串`substr($x->word, 0, $x->len)`
```C
  for (x = words; x->word; x++) {
    printf("%*.*s\n", x->len, x->len, x->word);
  }
``


# 用法示例

## 编译动态库
`make libjieba.so`

## 运行
`time php demo.php`

## 运行c demo
```
make demo
time ./demo
```
## 结果
```
PHP
load: 0.00025701522827148

real    1m59.619s
user    1m56.093s
sys     0m3.517s


C
real    1m54.738s
user    1m50.382s
sys     0m4.323s
```
CPU 占用  基本都是 12%

可以发现使用FFI，PHP的速度基本和C差不多，如有CPU占用大的业务，可以尝试使用其它语言编写然后生成标准C的动态库。



## 原 Readme
[README](README_cjieba.md)

