from pydantic_settings import BaseSettings
from typing import Literal


class Settings(BaseSettings):
    log_level: str = "info"
    cuda_visible_devices: str = "-1"

    api_host: str = "0.0.0.0"
    api_port: int = 8000
    metrics_port: int = 9090
    tf_serving_port: int = 8501

    models_cache: str = "/app/models_cache"

    model_config = {"env_file": ".env", "env_file_encoding": "utf-8"}


settings = Settings()
