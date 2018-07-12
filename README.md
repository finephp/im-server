# 即时聊天-服务端
## 环境变量配置
 APP_WORKER_COUNT=4   # 运行进程数
 GATEWAY_REGISTER_URL=127.0.0.1:8585 # 注册服务器地址（分布式用）
 GATEWAY_LANIP=127.0.0.1 #果是分布式的话不允许是127.0.0.1 ，必须是内网ip
 GATEWAY_START_PORT=2300 # 内部通迅端口
 ENV_NOREDIS=true #是否启用redis异步处理消息 true/false  
 SIGNATURE_FLAG=true # 是否开启签名验证 true/false  
 MC_APP_MASTERKEY=mc_app_masterkey #masterkey 签名用    
 MC_APP_ID=app_id #masterkey app_id      
 REDIS_HOST=127.0.0.1 #REDIS地址  
 REDIS_PORT=127.0.0.1 #REDIS端口  
 DB_HOST=127.0.0.1 #MONGO数据库地址    
 DB_PORT=27017 #MONGO数据库端口  
 DB_NAME=f10Data3 #MONGO数据库名  
 CLOUD_URL=http://cloud.com #第三方hook地址  
 IM_SOCKET_HOST=imserver #restful 内部通知地址  
 IM_SOCKET_PORT=2208 # restful 内部通知端口
 
 
 