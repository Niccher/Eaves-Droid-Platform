from sqlalchemy import create_engine, text
import pandas as pd

_engine = None


def get_db_engine(conn_str: str | None = None):
    global _engine
    if _engine is None and conn_str:
        _engine = create_engine(conn_str)
    return _engine


def query_to_df(query: str, conn_str: str | None = None) -> pd.DataFrame:
    engine = get_db_engine(conn_str)
    if engine is None:
        return pd.DataFrame()
    with engine.connect() as conn:
        return pd.read_sql(text(query), conn)
