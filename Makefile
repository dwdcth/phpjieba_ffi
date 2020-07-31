all: demo
demo: libjieba.a
	gcc -o demo demo.c -L./ -ljieba -lstdc++ -lm -static
libjieba.a:
	g++ -o jieba.o -c -DLOGGING_LEVEL=LL_WARNING -I./deps/ lib/jieba.cpp
	ar rs libjieba.a jieba.o 
clean:
	rm -f *.a *.o demo *.so

libjieba.so:
	g++ -fPIC -o libjieba.o -c -DLOGGING_LEVEL=LL_WARNING -I./deps/ lib/jieba.cpp
	g++ -shared -o libjieba.so libjieba.o