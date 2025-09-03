# 抽奖系统数据库设计规范

## 字段命名规范
- 主键统一使用 `id`
- 时间字段统一使用 `created_at`, `updated_at`, `deleted_at` (timestamp格式)
- 状态字段统一使用 `status` (字符串枚举)
- 外键字段统一使用 `{表名}_id` 格式
- 所有字段使用下划线命名法 (snake_case)

## 表结构设计

### 1. prizes (奖品表)
```sql
id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name            VARCHAR(100) NOT NULL COMMENT '奖品名称'
image           VARCHAR(255) NULL COMMENT '奖品图片URL'
price           DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '奖品价值'
stock           INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '库存数量'
type            ENUM('PHYSICAL','VIRTUAL') NOT NULL COMMENT '奖品类型：PHYSICAL=实物奖品，VIRTUAL=虚拟奖品'
email           VARCHAR(100) NULL COMMENT '联系邮箱'
status          ENUM('ACTIVE','INACTIVE') NOT NULL DEFAULT 'ACTIVE' COMMENT '状态：ACTIVE=启用，INACTIVE=禁用'
created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
deleted_at      TIMESTAMP NULL
```

### 2. lottery_codes (抽奖码表)
```sql
id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
code            VARCHAR(50) NOT NULL UNIQUE COMMENT '抽奖码'
prize_id        INT UNSIGNED NULL COMMENT '绑定奖品ID，NULL表示随机抽奖'
status          ENUM('UNUSED','USED') NOT NULL DEFAULT 'UNUSED' COMMENT '使用状态'
user_email      VARCHAR(100) NULL COMMENT '使用者邮箱'
used_at         TIMESTAMP NULL COMMENT '使用时间'
created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
deleted_at      TIMESTAMP NULL
```

### 3. draw_records (抽奖记录表)
```sql
id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
user_email      VARCHAR(100) NOT NULL COMMENT '用户邮箱'
lottery_code_id INT UNSIGNED NOT NULL COMMENT '抽奖码ID'
prize_id        INT UNSIGNED NULL COMMENT '中奖奖品ID'
status          ENUM('LOSE','WIN','INVALID') NOT NULL COMMENT '抽奖结果：LOSE=未中奖，WIN=中奖，INVALID=无效'
ip_address      VARCHAR(45) NULL COMMENT '抽奖IP地址'
user_agent      TEXT NULL COMMENT '用户代理信息'
created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
deleted_at      TIMESTAMP NULL
```

## 外键关系
- lottery_codes.prize_id -> prizes.id (SET NULL)
- draw_records.lottery_code_id -> lottery_codes.id (CASCADE)
- draw_records.prize_id -> prizes.id (SET NULL)

## 索引设计
- prizes: idx_status, idx_type, idx_deleted_at
- lottery_codes: idx_code, idx_status, idx_prize_id, idx_deleted_at
- draw_records: idx_user_email, idx_lottery_code_id, idx_prize_id, idx_status, idx_created_at, idx_deleted_at

## 数据一致性规则
1. 所有表必须包含软删除字段 deleted_at
2. 状态字段统一使用枚举类型，避免魔法数字
3. 时间字段统一使用 TIMESTAMP 类型
4. 外键关系明确，保证数据完整性
5. 字段命名保持一致性，避免同义词混用