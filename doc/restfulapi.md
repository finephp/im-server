
 
 ## restful api
 ### 删除消息   
     curl -X DELETE \
       -H "X-LC-Id: {{appid}}" \
       -H "X-LC-Key: {{masterkey}},master" \
       -G \
       --data-urlencode 'from_client=some-client-id' \
       --data-urlencode 'timestamp=123' \
       https://ip:8581/rtm/chatrooms/{chatroom_id}/messages/{message_id}
       
 ### 修改消息
       
       该接口要求使用 master key。 从 Objective-C SDK v6.0.0、Android SDK v4.4.0、JavaScript SDK v3.5.0 开始，我们支持了新的修改与撤回消息功能。修改或撤回消息后，即使已经收到并已缓存在客户端的消息也会被修改或撤回。对于老版本的 SDK，仅能修改或撤回服务器端的消息记录，并不能修改或撤回客户端已缓存的消息记录。
       +
       
       curl -X PUT \
         -H "X-LC-Id: {{appid}}" \
         -H "X-LC-Key: {{masterkey}},master" \
         -H "Content-Type: application/json" \
         -d '{"from_client": "", "message": "", "timestamp": 123}' \
         http://ip:8581/rtm/messages/logs
        
       +
       参数	约束	说明
       from_client	必填	消息的发件人 client ID
       message	必填	消息体
       timestamp	必填	消息的时间戳
       返回：
       +
       
       {"result": {}}
     
      
     
     
