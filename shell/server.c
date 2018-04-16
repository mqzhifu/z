/*socket tcp服务器端*/


//引入相关函数文件
#include <sys/stat.h>
#include <fcntl.h>
#include <errno.h>
#include <netdb.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>

#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <unistd.h>

#define SERVER_PORT 5555

int main(){
    //调用socket函数返回的文件描述符
	int serverSocket;
    //声明两个套接字sockaddr_in结构体变量，分别表示客户端和服务器
    struct sockaddr_in server_addr;
    struct sockaddr_in clientAddr;
    int addr_len = sizeof(clientAddr);
    int client;
    char buffer[200];
    int iDataNum;

    //计数器，无用
    int cnt = 0;

    //创建socket函数FD，失败返回-1
    //int socket(int domain, int type, int protocol);
    //参数1：使用的地址类型，一般都是ipv4，AF_INET
    //参数1：套接字类型，tcp：面向连接的稳定数据传输SOCK_STREAM
    //参数3：设置为0
	if((serverSocket = socket(AF_INET, SOCK_STREAM, 0)) < 0)
	{
		perror("create socket failed...");
		return 1;
	}


	printf("create socket ok.\n");

    //将两个结构体的值初始化归0
	bzero(&server_addr, sizeof(server_addr));

    //初始化服务器端的套接字，并用htons和htonl将端口和地址转成网络字节序
	server_addr.sin_family = AF_INET;
	server_addr.sin_port = htons(SERVER_PORT);

	//ip可是本服务器的ip，也可以用宏INADDR_ANY代替，代表0.0.0.0，表明所有地址
    server_addr.sin_addr.s_addr = htonl(INADDR_ANY);


    int reuse = 1;
    if (setsockopt(serverSocket, SOL_SOCKET, SO_REUSEADDR, (char *)&reuse, sizeof(int)) == -1) {
          error("Can't set the reuse option on the socket");
    }

    //对于bind，accept之类的函数，里面套接字参数都是需要强制转换成(struct sockaddr *)
    //bind三个参数：服务器端的套接字的文件描述符，
    if(bind(serverSocket, (struct sockaddr *)&server_addr, sizeof(server_addr)) < 0)
    {
        perror("bind connect failed..");
        return 1;
    }

    printf("bind socket ok.\n");


    //设置服务器上的socket为监听状态
    if(listen(serverSocket, 5) < 0)
    {
        perror("listen");
        return 1;
    }


    printf("Listening on port: %d\n", SERVER_PORT);

    while(1){
        //调用accept函数后，会进入阻塞状态
        //accept返回一个套接字的文件描述符，这样服务器端便有两个套接字的文件描述符，
        //serverSocket和client。
        //serverSocket仍然继续在监听状态，client则负责接收和发送数据

        //clientAddr是一个传出参数，accept返回时，传出客户端的地址和端口号
        //addr_len是一个传入-传出参数，传入的是调用者提供的缓冲区的clientAddr的长度，以避免缓冲区溢出。
        //传出的是客户端地址结构体的实际长度。
        //出错返回-1

        client = accept(serverSocket, (struct sockaddr*)&clientAddr, (socklen_t*)&addr_len);
        if(client < 0){
            perror("accept");
            continue;
        }

        printf("accept client:%d.\n",client);


        printf("\nrecv client data...\n");
        //inet_ntoa   ip地址转换函数，将网络字节序IP转换为点分十进制IP
        //表达式：char *inet_ntoa (struct in_addr);
        printf("IP is %s\n", inet_ntoa(clientAddr.sin_addr));
        printf("Port is %d\n", htons(clientAddr.sin_port));
        while(1){
            iDataNum = recv(client, buffer, 1024, 0);
            if(iDataNum < 0)
            {
                perror("recv");
                continue;
            }
//            buffer[iDataNum] = '\0';
            if(strcmp(buffer, "\0") == 0)
                break;

            cnt++;
            if(cnt > 10)
                break;



        }


        char sendata[] = ",yes!";
        strcat(buffer,sendata);
        printf(buffer);
        printf("%drecv data is %s\n", iDataNum, buffer);
        send(client, buffer, iDataNum, 0);

    }



    return 0;


}