from pydantic_settings import BaseSettings


class Settings(BaseSettings):
    # Runtime
    log_level: str = "info"
    cuda_visible_devices: str = "-1"

    # Server
    api_host: str = "0.0.0.0"
    api_port: int = 8000
    metrics_port: int = 9090
    tf_serving_port: int = 8501

    # Model cache
    models_cache: str = "/app/models_cache"

    # Database (shared MySQL with the PHP web app)
    db_host: str = "mysql"
    db_port: int = 3306
    db_name: str = "db_eaves_droid"
    db_user: str = "root"
    db_password: str = "root_password"

    model_config = {"env_file": ".env", "env_file_encoding": "utf-8"}


settings = Settings()
